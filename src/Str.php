<?php

declare(strict_types=1);

namespace DOF\Util;

use Closure;
use Throwable;
use DOF\Util\IS;
use DOF\Util\Exceptor;

class Str
{
    public static function wraps(
        array $list,
        string $wrapper,
        string $delimiter = ',',
        string $default = null
    ) : ?string {
        $_list = [];

        if (IS::array($list, 'assoc')) {
            return static::wrap(JSON::encode($list), $wrapper);
        }

        if (IS::array($list, 'index')) {
            foreach ($list as $key => $val) {
                if (! TypeHint::string($val)) {
                    continue;
                }

                $_list[] = static::wrap($val, $wrapper);
            }
        }

        return $_list === [] ? $default : \join($delimiter, $_list);
    }

    public static function wrap($str, string $wrapper, string $default = null) : ?string
    {
        if (! TypeHint::string($str)) {
            return $default;
        }

        $str = TypeCast::string($str);
        if (IS::empty($str)) {
            return $default;
        }

        return \sprintf("%s%s%s", $wrapper, $str, $wrapper);
    }

    public static function buffer($action, Closure $exception = null) : string
    {
        try {
            $level = \ob_get_level();
            \ob_start();

            if (IS::closure($action)) {
                $action();
            } else {
                \print_r($action);
            }

            return (string) \ob_get_clean();
        } catch (Throwable $th) {
            while (\ob_get_level() > $level) {
                \ob_end_clean();
            }

            if ($exception) {
                $exception($th);
            } else {
                throw new Exceptor('GET_BUFFER_STRING_EXCEPTION', $th);
            }
        }

        return '';
    }

    public static function partition(string $key, int $moduloDividendLength = 8) : int
    {
        return \hexdec(\substr(\md5(\strtolower($key)), 0, $moduloDividendLength));
    }

    public static function id(string $list, string $separator = ',', bool $unique = true) : array
    {
        return Arr::id(static::arr($list, $separator), $unique);
    }

    public static function mask(string $str, int $start, int $end, string $mask = '*') : string
    {
        return static::first($str, $start -1).\str_repeat($mask, \abs($end - $start + 1)).static::last($str, $end, false);
    }

    public static function mess(string $string) : string
    {
        $array = $_array = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        $count = \count($array);
        $result = '';

        for ($i = 0; $i < $count; $i++) {
            $_array = \array_keys($_array);
            $max = \count($_array) - 1;
            $idx = \mt_rand(0, $max);
            $chr = $array[$_array[$idx]] ?? '';
            $result .= $chr;

            unset($_array[$idx]);
        }

        return $result;
    }

    public static function contain(string $haystack, string $needle) : bool
    {
        return (\mb_strpos($haystack, $needle) !== false);
    }

    public static function last(string $raw, int $count, bool $reverse = true) : string
    {
        $len = \mb_strlen($raw);
        if ($count >= $len) {
            return $reverse ? $raw : '';
        }

        $start = $reverse ? $len - $count : $count;
        $length = $reverse ? null : $len - $count;
        return \mb_substr($raw, $start, $length);
    }

    public static function first(string $raw, int $count) : string
    {
        $len = \mb_strlen($raw);
        if ($count >= $len) {
            return $raw;
        }

        return \mb_substr($raw, 0, $count);
    }

    public static function fixed(string $raw, int $limit, string $fill = ' ...... ') : string
    {
        $len = \mb_strlen($fill);
        if ($limit < 1) {
            return '';
        }
        if ($limit < $len) {
            return \mb_substr($raw, 0, $limit);
        }

        $length = \mb_strlen($raw);
        if ($length <= ($len-2)) {
            return $raw;
        }
        if ($length <= $limit) {
            return $raw;
        }

        $limit = $limit - $len;

        $half = \intval(\abs(\floor($limit/2)));

        $res  = \mb_substr($raw, 0, $limit - $half);
        $res .= $fill;
        $res .= \mb_substr($raw, -($half), $half);

        return $res;
    }

    public static function middle(string $str, $start, $end) : string
    {
        if (\is_int($start)) {
        } elseif (\is_string($start)) {
            $start = \mb_strlen($start);
        } else {
            $start = 1;
        }
        if (\is_int($end)) {
        } elseif (\is_string($end)) {
            $end = \mb_strlen($end);
        } else {
            $end = 1;
        }

        return \mb_substr($str, $start, (\mb_strlen($str) - $start - $end));
    }

    public static function shift(string $str, $shift, bool $reverse = false) : string
    {
        if (\is_int($shift)) {
        } elseif (\is_string($shift)) {
            $shift = \mb_strlen($shift);
        } else {
            $shift = 1;
        }

        if ($reverse) {
            return \mb_substr($str, 0, (\mb_strlen($str) - $shift));
        }

        return \mb_substr($str, $shift);
    }

    public static function start(string $needle, string $haystack) : bool
    {
        $length = \mb_strlen($needle);

        if (0 === $length) {
            return false;
        }

        return (\mb_substr($haystack, 0, $length) === $needle);
    }

    public static function end(string $needle, string $haystack, bool $ci = false) : bool
    {
        $length = \mb_strlen($needle);
        if ((0 === $length) || (0 === \mb_strlen($haystack))) {
            return false;
        }

        if ($ci) {
            $haystack = \strtolower($haystack);
            $needle = \strtolower($needle);
        }

        return (\mb_substr($haystack, -$length) === $needle);
    }

    /**
     * Check case sensitivity of a character
     *
     * @param string $char
     * @param int $case: 0-lower; 1-upper
     */
    public static function charcase(string $char, int $case) : bool
    {
        if (0 === $case) {
            $asciiStart = 97;
            $asciiEnd = 122;
            $preg = '/[a-z]/u';
        } elseif (1 === $case) {
            $asciiStart = 65;
            $asciiEnd = 90;
            $preg = '/[A-Z]/u';
        } else {
            return false;
        }

        $ascii = \ord($char);
        // A-Z ASCII number range => 65~90
        // a-z ASCII number range => 97~122
        return (false
            || (($asciiStart <= $ascii) && ($ascii <= $asciiEnd))
            || (1 === \preg_match($preg, $char))
        );
    }

    public static function eq($v1, $v2, bool $ci = false) : bool
    {
        return static::equal($v1, $v2, $ci);
    }

    public static function equal($v1, $v2, bool $ci = false) : bool
    {
        if ((! \is_string($v1)) && (! \is_numeric($v1))) {
            return false;
        }
        if ((! \is_string($v2)) && (! \is_numeric($v2))) {
            return false;
        }

        if ($ci) {
            return \strtolower(\strval($v1)) === \strtolower(\strval($v2));
        }

        return \strval($v1) === \strval($v2);
    }

    public static function arr(string $str, string $explode = ',', bool $trim = true) : array
    {
        $str = \trim($str);
        $arr = \explode($explode, $str);

        if ($trim) {
            return Arr::trim($arr);
        }

        return $arr;
    }

    public static function literal($val) : string
    {
        if (\is_null($val)) {
            return 'null';
        }
        if (\is_bool($val)) {
            return ($val ? 'true' : 'false');
        }
        if (\is_scalar($val)) {
            return (string) $val;
        }

        return \gettype($val);
    }
}
