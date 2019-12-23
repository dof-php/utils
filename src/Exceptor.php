<?php

declare(strict_types=1);

namespace DOF\Util;

use Throwable;
use Exception;
use DOF\Util\IS;
use DOF\Util\Str;

// Exception reporter: a dynamic and flexible way to handle exceptions
class Exceptor extends Exception
{
    const TAG_CLIENT = 'client_exception';
    const TAG_SERVER = 'server_exception';

    /** @var string|numberic: No of current exceptor name */
    public $no;

    /** @var string: Description of current exceptor with $this->name */
    public $info;

    /** @var string: Dynamic minor name, can replace Exceptor class name if $this->proxy is true */
    public $name;

    /** @var array: Placeholder of Exceptor for origin PHP Exception trace */
    public $chain;

    /** @var array: Context details of this name of Exceptor */
    public $context = [];

    /** @var array: User defined tags on current exceptor */
    public $tags = [];

    /** @var string: Suggestions for current exceptor */
    public $suggestion;

    /** @var array: Suggestions for children of current exceptor */
    public $advices = [];

    /** @var boolean: $this->name is the REAL exceptor name or not */
    public $proxy = true;

    public function __construct(...$params)
    {
        $code = -1;
        $previous = null;

        foreach ($params as $value) {
            if (\is_null($value)) {
                continue;
            }
            if (\is_array($value)) {
                $this->context = $value;
            } elseif (\is_string($value)) {
                $this->name = $value;
            } elseif (IS::throwable($value)) {
                $previous = $value;
            } elseif (\is_int($value)) {
                $this->no = $code = $value;
            } elseif (\is_bool($value)) {
                $this->proxy = $value;
            } elseif (IS::closure($value)) {
                $value($this);
            }
        }

        parent::__construct($this->info ?? static::class, $code, $previous);
    }

    public function setNo($no)
    {
        if (\is_numeric($no) || \is_string($no)) {
            $this->no = $no;

            if (\is_int($no)) {
                // Compatible with Exception::getCode()
                $this->code = $no;
            }
        }

        return $this;
    }

    public function getNo()
    {
        return $this->no ?? $this->getCode();
    }

    public function hasTag(string $tag) : bool
    {
        return \array_key_exists(\strtolower($tag), $this->tags);
    }

    public function getTag(string $tag, $default = null)
    {
        $value = $this->tags[\strtolower($tag)] ?? null;

        return \is_null($value) ? $default : $value;
    }

    public function getTags() : array
    {
        return $this->tags;
    }

    public function tag(string $tag, $value = null)
    {
        $this->tags[\strtolower($tag)] = $value;

        return $this;
    }

    public function setInfo(string $info = null)
    {
        $this->info = $info;

        if (! \is_null($info)) {
            // Compatible with Exception::getMessage()
            $this->message = $info;
        }

        return $this;
    }

    public function getInfo()
    {
        return $this->info ?? static::class;
    }

    public function getChain()
    {
        return $this->chain ?? $this->getTrace();
    }

    public function setChain(array $chain)
    {
        $this->chain = $chain;

        return $this;
    }

    public function setName(string $name = null)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        if ($this->proxy && $this->name) {
            return $this->name;
        }

        return Reflect::getObjectName($this);
    }

    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setProxy(bool $proxy)
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    public function setSuggestion(string $suggestion = null)
    {
        $this->suggestion = $suggestion;

        return $this;
    }

    public function getSuggestion()
    {
        return $this->getSuggestion;
    }

    public function setAdvices(array $advices)
    {
        $this->advices = $advices;

        return $this;
    }

    public function getAdvices()
    {
        return $this->advices;
    }
}
