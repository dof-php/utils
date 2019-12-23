<?php

declare(strict_types=1);

namespace DOF\Util;

use Throwable;
use DOF\Util\Str;
use DOF\Util\IS;
use DOF\Util\Exceptor;
use DOF\Util\Exceptor\ValidatorExceptor;
use DOF\Util\Exceptor\ValidationFailure;
use DOF\Util\Exceptor\TypeHintExceptor;

/**
 * General data format validator
 *
 * Two types of exceptions will be thrown in this validator:
 * - Server side: eg, definition errors of validation rules
 * - Client side: eg, given data validation failed aginst given rules
 */
class Validator
{
    const DEFAULT_ERRMSG = [
        'REQUIRE_PARAMETER' => 'Missing or empty parameter: `:key:`. (:rule:: :option:)',
        'UNACCEPTABLE_TYPE' => 'Unacceptable type: `:key:` is not :option:. (:value:)',
        'VALIDATION_FAILED' => 'Validation failed: :rule:(:option:, :key:). (:value:)',
    ];

    /** @var array: The data origin to be validated */
    protected $data = [];

    /** @var array: The rules used to validate given data */
    protected $rules = [];

    /** @var array: The extra parameters used to validate given data */
    protected $extra = [];

    /** @var array: The error messages used to output when validation failed */
    protected $errs = [];

    /** @var bool: Whether abort validation process when first rule run fails */
    protected $abortOnFail = true;

    /** @var bool: Whether throw an validation exception when first rule run fails */
    protected $throwOnFail = true;

    /** @var array: A List of failed validations */
    protected $fails = [];

    /** @var array: The validated result */
    protected $result;

    public function execute()
    {
        foreach ($this->rules as $key => $rules) {
            $value = $_value = $this->data[$key] ?? null;

            if (IS::empty($value) && \array_key_exists('default', $rules)) {
                $value = $this->result[$key] = $rules['default'] ?? null;
            }

            if ($require = ($rules['require'] ?? false)) {
                list($rule, $option) = $require;
                $validate = $this->validate($rule, $value, $key, $option);
                // null means no need for current value
                if (null === $validate) {
                    // skip only if no need and empty value
                    if (IS::empty($value)) {
                        continue;
                    }
                } elseif (true !== $validate) {
                    $this->throw('REQUIRE_PARAMETER', $rule, $key, $option, $value);
                    continue;
                }
            } else {
                if (IS::empty($value)) {
                    continue;
                }
            }

            if (\array_key_exists('type', $rules)) {
                $type =  $rules['type'] ?? null;
                if ((! $type) || (! TypeHint::typehint($type, $value))) {
                    $this->throw('UNACCEPTABLE_TYPE', 'type', $key, $type, \join(': ', [\gettype($value), Str::literal($value)]));
                    continue;
                }

                $this->result[$key] = $_value = TypeCast::typecast($type, $value);
            }

            foreach ($rules['normal'] ?? [] as $rule => $option) {
                if (true === $this->validate($rule, $_value, $key, $option)) {
                    continue;
                }

                $this->throw('VALIDATION_FAILED', $rule, $key, $option, \join(': ', [$value, \mb_strlen($value)]));
            }
        }

        return $this;
    }

    private function validate(string $rule, &$value, string $key, $option) : ?bool
    {
        if (\method_exists($this, ($_rule = "__{$rule}"))) {
            return $this->{$_rule}($key, $option, $value);
        }
        if (\method_exists(IS::class, $rule)) {
            return IS::{$rule}($value, $option);
        }

        throw new ValidatorExceptor('UNSUPPORTED_VALIDATION_RULE', \compact('rule', 'key', 'option', 'value'));
    }

    private function __formatwhen(string $key, string $format, &$value) : string
    {
        $when = $this->extra[$key]['formatwhen'] ?? null;
        if (! $when) {
            // $value = null;    // ignore this key when expected FormatWhen not exsits
            return true;
        }
        $_key = $when['key'] ?? null;
        if ((! $_key) || (! isset($this->data[$_key]))) {
            return true;
        }
        $_val = $when['val'] ?? null;
        $__val = $this->data[$_key] ?? null;
        if ($_val !== $__val) {
            return true;
        }

        if (! \method_exists(IS::class, $format)) {
            throw new ValidatorExceptor('FORMAT_FUNCTION_UNDEFINED', \compact('format'));
        }

        return IS::{$format}($this->data[$key] ?? null);
    }

    private function __regexwhen(string $key, string $regex, &$value) : bool
    {
        $when = $this->extra[$key]['regexwhen'] ?? null;
        if (! $when) {
            // $value = null;    // ignore this key when expected RegexWhen not exsits
            return true;
        }
        $_key = $when['key'] ?? null;
        if ((! $_key) || (! isset($this->data[$_key]))) {
            return true;
        }
        $_val = $when['val'] ?? null;
        $__val = $this->data[$_key] ?? null;
        if ($_val !== $__val) {
            return true;
        }

        $value = $this->data[$key] ?? null;
        if (\is_null($value)) {
            return false;
        }

        return \preg_match("/{$regex}/", \strval($value)) === 1;
    }

    private function __needifhas(string $key, string $has, &$value) : ?bool
    {
        if (IS::empty($this->data[$has] ?? null)) {
            $value = null;    // ignore this key when expected key not exsits
            return null;
        }

        return IS::empty($this->data[$key] ?? null);
    }

    private function __needifno(string $key, string $no, &$value) : ?bool
    {
        if (IS::empty($this->data[$no] ?? null)) {
            return !IS::empty($this->data[$key] ?? null);
        }

        $value = null;    // ignore this key when unexpected key exsits

        return null;
    }

    private function __need(string $key, $need, &$value) : ?bool
    {
        $empty = IS::empty($this->data[$key] ?? null);
        if (IS::confirm($need)) {
            return !$empty;
        }

        return $empty ? null : true;
    }

    public function throw(
        string $name,
        string $rule,
        string $key,
        $option = null,
        $value = null,
        array $context = []
    ) {
        $preg = function ($info) use ($rule, $key, $option, $value) {
            $patterns = ['(:key:)', '(:option:)', '(:rule:)', '(:value:)'];
            $replaces = [$key, Str::literal($option), $rule, Str::literal($value)];

            return \preg_replace($patterns, $replaces, $info);
        };

        $errmsg = Validator::DEFAULT_ERRMSG[$name] ?? null;

        $_rule = \strtoupper($rule);
        if ($_err = $this->extra[$key][$_rule]['ERR'] ?? null) {
            if (IS::confirm($this->extra[$key][$_rule]['ERR_PREG'] ?? null)) {
                $errmsg = $preg($_err);
            } else {
                $errmsg = $_err;
            }
        } elseif ($_err = $this->errs[$key][$rule] ?? null) {
            $errmsg = $preg($_err);
        } else {
            $errmsg = $preg($errmsg);
        }

        $context = \compact('rule', 'key', 'option', 'value', 'context');
        if ($this->throwOnFail) {
            throw new ValidationFailure($name, $context, function ($exceptor) use ($errmsg) {
                $exceptor->tag(Exceptor::TAG_CLIENT)->setInfo($errmsg);
            });
        }

        $this->fails[] = [$name, $errmsg, $context];

        return $this;
    }

    public function throwOnFail(bool $throwOnFail)
    {
        $this->throwOnFail = $throwOnFail;
    
        return $this;
    }

    public function abortOnFail(bool $abortOnFail)
    {
        $this->abortOnFail = $abortOnFail;
    
        return $this;
    }

    /**
     * Setter for data
     *
     * @param array $data
     * @return Validator
     */
    public function setData(array $data)
    {
        $this->data = $this->result = $data;
    
        return $this;
    }

    /**
     * Setter for rules
     *
     * @param array $rules
     * @return Validator
     */
    public function setRules(array $rules)
    {
        $this->rules = Validator::parse($rules);

        return $this;
    }

    /**
     * Setter for extra
     *
     * @param array $extra
     * @return Validator
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
    
        return $this;
    }

    /**
     * Setter for errs
     *
     * @param array $errs
     * @return Validator
     */
    public function setErrs(array $errs)
    {
        $this->errs = $errs;
    
        return $this;
    }

    /**
     * Getter for fails
     *
     * @return array
     */
    public function getFails()
    {
        return $this->fails;
    }

    /**
     * Getter for result
     *
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    public static function parse(array $_rules) : array
    {
        $result = [];

        foreach ($_rules as $key => $rules) {
            if (! \is_string($key)) {
                throw new ValidatorExceptor('INVALID_VALIDATE_KEY', \compact('key'));
            }

            foreach ($rules as $rule => $option) {
                if (\is_int($rule)) {
                    $rule = $option;
                    $option = null;
                }
                if ((! \is_string($rule)) || IS::empty($rule)) {
                    throw new ValidatorExceptor('INVALID_VALIDATE_RULE', \compact('rule'));
                }

                switch ($rule = \strtolower($rule)) {
                    case 'default':
                        $result[$key]['default'] = $option;
                        break;
                    case 'type':
                        $type = $option;
                        if (false
                            || (! \is_string($type))
                            || empty($type = \trim(\strtolower($type)))
                            || (! TypeHint::support($type))
                        ) {
                            throw new TypeHintExceptor('UNTYPEHINTABLE_TYPE', \compact('type'));
                        }

                        $result[$key]['type'] = $type;
                        break;
                    case 'need':
                    case 'needifhas':
                    case 'needifno':
                        if ($result[$key]['require'] ?? null) {
                            throw new ValidatorExceptor('MULTIPLE_REQUIREMENT_RULES', \compact('key', 'rules'));
                        }
                        $result[$key]['require'] = [$rule, $option];
                        break;
                    default:
                        $result[$key]['normal'][$rule] = $option;
                        break;
                }
            }
        }

        return $result;
    }
}
