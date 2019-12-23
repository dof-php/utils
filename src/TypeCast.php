<?php

declare(strict_types=1);

namespace DOF\Util;

use Throwable;

use DOF\Util\Exceptor\TypeCastExceptor;

class TypeCast
{
    /**
     * type-casting a value to a given type with force option
     *
     * force option only works when given type is actually force-type-casting-able
     *
     * @param string $type
     * @param mixed $value
     * @param bool $force: force type-casting into basic type when $value is not type $type
     * @return mixed
     */
    public static function typecast(string $type, $value = null, bool $force = true)
    {
        $cast = \strtolower(\trim($type));

        if (static::support($cast)) {
            return static::{$cast}($value, $force);
        }

        throw new TypeCastExceptor('UNTYPECASTABLE_VALUE', \compact('type', 'value', 'force'));
    }

    public static function support(string $type) : bool
    {
        return \method_exists(static::class, \strtolower(\trim($type)));
    }

    public static function bool($value, bool $force = false, string $type = 'bool') : bool
    {
        if (TypeHint::bool($value)) {
            return \boolval($value);
        }

        if ($force && IS::empty($value)) {
            return false;
        }

        if ($force && \is_scalar($value)) {
            return \boolval($value);
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function boolean($value, bool $force = false, string $type = 'boolean') : bool
    {
        return TypeHint::bool($value, $force, $type);
    }

    public static function int($value, bool $force = false, string $type = 'int') : int
    {
        if (TypeHint::int($value)) {
            return \intval($value);
        }

        if ($force && IS::empty($value)) {
            return 0;
        }

        if ($force && \is_scalar($value)) {
            return \intval($value);
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function integer($value, bool $force = false, string $type = 'integer')
    {
        return TypeHint::int($value, $force, 'integer');
    }

    public static function string($value, bool $force = false, string $type = 'string') : string
    {
        if (TypeHint::string($value)) {
            return \strval($value);
        }

        if (true
            && \is_object($value)
            && \method_exists($value, '__toString')
            && \is_string($_value = $value->__toString())
        ) {
            return $_value;
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function float($value, bool $force = false, string $type = 'float') : float
    {
        if (TypeHint::float($value)) {
            return \floatval($value);
        }

        if ($force && IS::empty($value)) {
            return 0.0;
        }

        if ($force && \is_scalar($value)) {
            return \floatval($value);
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function double($value, bool $force = false, string $type = 'double') : double
    {
        return TypeHint::float($value, $force, $type);
    }

    public static function array($value, bool $force = false, string $type = 'array') : array
    {
        if (TypeHint::array($value)) {
            return (array) $value;
        }

        if ($force && IS::empty($value)) {
            return [];
        }

        if (true
            && \is_object($value)
            && \method_exists($value, '__toArray')
            && \is_array($_value = $value->__toArray())
        ) {
            return $_value;
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function bint($value, bool $force = false, string $type = 'bint')
    {
        if (TypeHint::bint($value)) {
            return \intval($value);
        }

        if ($force && IS::empty($value)) {
            return 0;
        }
        if ($force && \is_scalar($value)) {
            return \intval(\boolval($value));
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function uint($value, bool $force = false, string $type = 'uint')
    {
        if (TypeHint::uint($value)) {
            return \intval($value);
        }

        if ($force && IS::empty($value)) {
            return 0;
        }
        if ($force && \is_scalar($value)) {
            return \intval($value);
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function pint($value, bool $force = false, string $type = 'pint')
    {
        if (TypeHint::pint($value)) {
            return \intval($value);
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function nint($value, bool $force = false, string $type = 'pint')
    {
        if (TypeHint::nint($value)) {
            return \intval($value);
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function list($value, bool $force = false, string $type = 'list') : array
    {
        if (TypeHint::list($value)) {
            return (array) $value;
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function namespace($value, bool $force = false)
    {
        if (TypeHint::namespace($value)) {
            return $value;
        }

        throw new TypeCastExceptor(\compact('value', 'type'));
    }

    public static function char($value, bool $force = false, string $type = 'char')
    {
        return static::string($value, $force, $type);
    }

    public static function mediumtext($value, bool $force = false, string $type = 'mediumtext')
    {
        return static::string($value, $force, $type);
    }

    public static function tinytext($value, bool $force = false, string $type = 'tinytext')
    {
        return static::string($value, $force, $type);
    }

    public static function longtext($value, bool $force = false, string $type = 'longtext')
    {
        return static::string($value, $force, $type);
    }

    public static function text($value, bool $force = false, string $type = 'text')
    {
        return static::string($value, $force, $type);
    }

    public static function varchar($value, bool $force = false, string $type = 'varchar')
    {
        return static::string($value, $force, $type);
    }

    public static function bigint($value, bool $force = false, string $type = 'bigint')
    {
        if (TypeHint::bigint($value)) {
            return (int) $value;
        }

        if ($force && IS::empty($value)) {
            return 0;
        }

        if ($force && \is_scalar($value)) {
            return \intval($value);
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function mediumint($value, bool $force = false, string $type = 'mediumint')
    {
        if ($force && IS::empty($value)) {
            $value = 0;
        } elseif ($force && \is_scalar($value)) {
            $value = \intval($value);
        }

        if (TypeHint::int($value)) {
            $value = (int) $value;
            if (Num::between($value, -8388608, 16777215)) {
                return $value;
            }
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function smallint($value, bool $force = false, string $type = 'smallint')
    {
        if ($force && IS::empty($value)) {
            $value = 0;
        } elseif ($force && \is_scalar($value)) {
            $value = \intval($value);
        }

        if (TypeHint::int($value)) {
            $value = (int) $value;
            if (Num::between($value, -32768, 65535)) {
                return $value;
            }
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function tinyint($value, bool $force = false, string $type = 'tinyint')
    {
        if ($force && IS::empty($value)) {
            $value = 0;
        } elseif ($force && \is_scalar($value)) {
            $value = \intval($value);
        }

        if (TypeHint::int($value)) {
            $value = (int) $value;
            if (Num::between($value, -128, 255)) {
                return $value;
            }
        }

        throw new TypeCastExceptor(\compact('value', 'force', 'type'));
    }

    public static function decimal($value, bool $force = false, string $type = 'decimal') : float
    {
        return static::float($value, $force, $type);
    }
}
