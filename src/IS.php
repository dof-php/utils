<?php

declare(strict_types=1);

namespace DOF\Util;

use DOF\Util\Arr;
use DOF\Util\Dict;
use DOF\Util\Iterate;
use DOF\Util\Exceptor;
use DOF\Util\Collection;

class IS
{
    public static function duck($object, string $method, ...$params) : bool
    {
        // TODO&FIXME
        // method: public
        // params: satisfy method's definitions

        return false;
    }

    public static function confirm($value, array $map = ['1', 'true', 'yes', 'y', 'on']) : bool
    {
        if (\is_bool($value)) {
            return $value === true;
        }
        if (\is_null($value)) {
            return false;
        }

        return IS::ciin($value, $map);
    }

    public static function type($value, string $type) : bool
    {
        if (! TypeHint::support($type)) {
            return false;
        }

        return TypeHint::{$type}($value);
    }

    public static function length($value, $length) : bool
    {
        if (! TypeHint::uint($length)) {
            return false;
        }
        $length = TypeCast::uint($length);

        if (TypeHint::string($value)) {
            return \mb_strlen(\strval($value)) === $length;
        }
        if (TypeHint::array($value)) {
            return \count($value) === $length;
        }

        return false;
    }

    public static function max($value, $max) : bool
    {
        if (! TypeHint::int($max)) {
            return false;
        }
        $max = TypeCast::int($max);

        if (TypeHint::int($value)) {
            $value = TypeCast::int($value);

            return $value <= $max;
        }

        if (TypeHint::string($value)) {
            $value = TypeCast::string($value);

            return \mb_strlen($value) <= $max;
        }

        if (TypeHint::array($value)) {
            $value = TypeCast::array($value);

            return \count($value) <= $max;
        }

        return false;
    }

    public static function min($value, $min) : bool
    {
        if (! TypeHint::int($min)) {
            return false;
        }
        $min = TypeCast::int($min);
        if (TypeHint::int($value)) {
            $value = TypeCast::int($value);

            return $value >= $min;
        }
        if (TypeHint::string($value)) {
            $value = TypeCast::string($value);

            return \mb_strlen($value) >= $min;
        }
        if (TypeHint::array($value)) {
            $value = TypeCast::array($value);

            return \count($value) >= $min;
        }

        return false;
    }
    public static function nint($value) : bool
    {
        if (! TypeHint::int($value)) {
            return false;
        }

        return TypeCast::int($value) < 0;
    }

    public static function bint($value) : bool
    {
        if (! TypeHint::int($value)) {
            return false;
        }

        return \in_array(TypeCast::int($value), [0, 1]);
    }

    public static function pint($value) : bool
    {
        if (! TypeHint::int($value)) {
            return false;
        }

        return TypeCast::int($value) > 0;
    }

    public static function uint($value) : bool
    {
        if (! TypeHint::int($value)) {
            return false;
        }

        return TypeCast::int($value) >= 0;
    }

    public static function bool($value) : bool
    {
        return TypeHint::bool($value);
    }

    public static function int($value) : bool
    {
        return TypeHint::int($value);
    }

    public static function string($value) : bool
    {
        return TypeHint::string($value);
    }

    public static function in($value, $target) : bool
    {
        if (TypeHint::array($target)) {
            return \in_array($value, $target);
        }

        if (TypeHint::string($target)) {
            return \in_array($value, Str::arr($target));
        }

        return false;
    }

    public static function url($value) : bool
    {
        return false !== \filter_var($value, FILTER_VALIDATE_URL);
    }

    public static function host($value) : bool
    {
        if ((! IS::string($value)) || IS::empty($value)) {
            return false;
        }

        $value = TypeCast::string($value);

        return (false
            || (false !== \filter_var($value, FILTER_VALIDATE_DOMAIN))
            || (false !== \filter_var($value, FILTER_VALIDATE_IP))
        );
    }

    public static function mobile($value, string $type = 'cn') : bool
    {
        if ((! $value) || (! \is_scalar($value))) {
            return false;
        }

        $value = (string) $value;

        switch ($type) {
            case 'cn':
            default:
                return 1 === \preg_match('#^(\+86[\-\ ])?1\d{10}$#', $value);
        }

        return true;    // TODO&FIXME
    }

    public static function ipv6(string $key) : bool
    {
        return \filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    public static function ipv4(string $key) : bool
    {
        return \filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    public static function ip($value) : bool
    {
        return false !== \filter_var($value, FILTER_VALIDATE_IP);
    }

    public static function email($value) : bool
    {
        return false !== \filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public static function dateformat($value, string $format = 'Y-m-d H:i:s') : bool
    {
        if (IS::empty($format) || IS::empty($value)) {
            return false;
        }

        if (! \is_string($value)) {
            return false;
        }

        $dt = \DateTime::createFromFormat($format, $value);

        return $dt && ($dt->format($format) == $value);
    }

    public static function timestamp($value) : bool
    {
        return TypeHint::pint($value);
    }

    public static function ciins(array $value, array $list) : bool
    {
        $list = \array_map(function ($item) {
            return \strtolower((string) $item);
        }, $list);

        foreach ($value as $val) {
            if (! IS::ciin($val, $list, false)) {
                return false;
            }
        }

        return true;
    }

    public static function ciin($value, array $list, bool $convert = true) : bool
    {
        if (! \is_scalar($value)) {
            return false;
        }

        $value = \strtolower((string) $value);

        if ($convert) {
            $list = \array_map(function ($item) {
                return \strtolower((string) $item);
            }, $list);
        }

        return \in_array($value, $list);
    }

    public static function id($value, string $option = null) : bool
    {
        switch ($option) {
            case 'array':
                if ((! $value) || (! \is_array($value))) {
                    return false;
                }

                return \count(Arr::id($value, false)) === \count($val);
            case 'list':
                if ((! $value) || (! \is_string($value))) {
                    return false;
                }

                $ids = Str::arr($value);

                return \count(Arr::id($ids, false)) === \count($ids);
            default:
                return IS::pint($value);
        }
    }

    public static function array($value, string $option = null) : bool
    {
        if (! \is_array($value)) {
            return false;
        }

        switch ($option) {
            case Arr::INDEX:
            case 'list':
                if ([] === $value) {
                    return false;
                }
                return \array_keys($value) === \range(0, (\count($value) - 1));
            case Arr::ASSOC:
            case 'object':
                if ([] === $value) {
                    return false;
                }
                return \count(\array_filter(\array_keys($value), 'is_string')) === \count($value);
            case 'scalar':
            case 'value':
                foreach ($value as $idx => $val) {
                    if (! \is_int($idx)) {
                        return false;
                    }
                    if (! \is_scalar($val)) {
                        return false;
                    }
                }
                return true;
            default:
                return true;
        }
    }

    public static function namespace($ns = null) : bool
    {
        return $ns && \is_string($ns) && (\class_exists($ns) || \interface_exists($ns) || \trait_exists($ns));
    }

    // DOF PHP only
    public static function err(int $code, array $err) : bool
    {
        return $code === ($err[0] ?? null);
    }

    // DOF PHP only
    public static function exceptor($value, $target = null) : bool
    {
        if ((! \is_object($value)) || (! ($value instanceof Exceptor))) {
            return false;
        }
        if (\is_null($target)) {
            return true;
        }
        if (\is_string($target) && \is_subclass_of($target, Exceptor::class)) {
            return ($value instanceof $target);
        }

        return ($value->getNo() === $target) || ($value->getName() === $target);
    }

    public static function collection($value) : bool
    {
        return \is_object($value) && ($value instanceof Collection);
    }

    public static function iterate($value) : bool
    {
        return \is_object($value) && ($value instanceof Iterate);
    }

    public static function dict($value) : bool
    {
        return \is_object($value) && ($value instanceof Dict);
    }

    public static function closure($value) : bool
    {
        return \is_object($value) && ($value instanceof \Closure);
    }

    public static function anonymous($value) : bool
    {
        return \is_object($value) && (new \ReflectionClass($value))->isAnonymous();
    }

    public static function throwable($value) : bool
    {
        return \is_object($value) && ($value instanceof \Throwable);
    }

    public static function empty($value) : bool
    {
        if (\is_null($value)) {
            return true;
        }
        if (\is_array($value)) {
            return $value === [];
        }
        if (\is_string($value)) {
            return \trim($value) === '';
        }

        return false;
    }
}
