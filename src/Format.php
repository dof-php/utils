<?php

declare(strict_types=1);

namespace DOF\Util;

use Closure;
use Throwable;
use DOF\Util\IS;
use DOF\Util\Str;
use DOF\Util\Arr;
use DOF\Util\JSON;
use DOF\Util\Reflect;
use DOF\Util\Exceptor;
use DOF\Util\Collection;
use DOF\Util\Paginator;
use DOF\Util\Exceptor\FormatExceptor;

final class Format
{
    public static function exceptor(...$params) : array
    {
        return Format::throwable((new Exceptor(...$params))->setChain(\debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    }

    /**
     * Parse throwable recursively with fixed format
     *
     * @param Throwable $throwable
     * @param array $context: Throwable context
     */
    public static function throwable(Throwable $th) : array
    {
        $data = [
            'code' => $th->getCode(),
            'info' => $th->getMessage(),
            'file' => $th->getFile(),
            'line' => $th->getLine(),
        ];

        if (IS::exceptor($th)) {
            $data['name'] = $th->getName();
            $data['code'] = $th->getNo();
            // $data['tags'] = $th->getTags();
            $data['more'] = $th->context;
            $data['call'] = [];
            if ($chain = $th->getChain()) {
                foreach ($chain as $idx => $trace) {
                    \extract($trace);

                    $item = "#{$idx} {$file}({$line})";
                    if (($class ?? false) && ($type ?? false) && ($function ?? false)) {
                        $item .= ": {$class}{$type}{$function}(...)";
                    }

                    $data['call'][] = $item;
                }
            }
        } else {
            $data['name'] = Reflect::getObjectName($th);
            $data['call'] = \explode(PHP_EOL, $th->getTraceAsString());
        }

        if ($previous = $th->getPrevious()) {
            $data['last'] = Format::throwable($previous);
        }

        return $data;
    }

    public static function scalar($value)
    {
        if (\is_scalar($value)) {
            return $value;
        }
        if (\is_object($value)) {
            if ($value instanceof Collection) {
                return Format::uncollect($value);
            }
            if ($value instanceof Paginator) {
                return Format::scalar($value->getList());
            }
            if (\method_exists($value, '__data__')) {
                return Format::scalar($value->__data__());
            }
            if (\method_exists($value, '__toArray')) {
                return Format::scalar($value->__toArray());
            }
            if (\method_exists($value, '__toString')) {
                return Format::scalar($value->__toString());
            }
        }
        if (\is_array($value)) {
            foreach ($value as $key => &$val) {
                $val = Format::scalar($val);
            }
            return $value;
        }

        return null;
    }

    public static function uncollect($value)
    {
        $data = $value;
        if (IS::collection($value)) {
            $data = $value->getData();
        } elseif (\is_array($value)) {
        } else {
            return $value;
        }

        $result = [];
        foreach ($data as $key => $item) {
            if (IS::collection($item)) {
                $result[$key] = Format::uncollect($item);
                continue;
            }

            $result[$key] = $item;
        }

        return $result;
    }

    public static function collect(array $data, $origin = null, bool $recursive = true)
    {
        if (! $recursive) {
            return (($data === []) || IS::array($data, Arr::ASSOC))
                ? new Collection($data, $origin)
                : $data;
        }

        foreach ($data as $key => &$value) {
            if (\is_array($value) && (($value === []) || IS::array($value, Arr::ASSOC))) {
                $value = Format::collect($value, null, $recursive);
            }
        }

        return (($data === []) || IS::array($data, Arr::ASSOC))
            ? new Collection($data, $origin, $recursive)
            : $data;
    }

    /**
     * Wrap an array-access data with given array wrapper, and callback if item is null
     */
    public static function wrap($data, array $wrapper = null, Closure $callback = null)
    {
        if (! $wrapper) {
            return $data;
        }

        $result = [];

        foreach ($wrapper as $_key => $default) {
            $key = \is_int($_key) ? $default : $_key;
            $val = null;

            if (\is_object($data)) {
                $val = ($data->{$default} ?? null) ?: ($data->{$_key} ?? null);

                if (\is_null($val) && \method_exists($data, '__toArray')) {
                    if (\is_array($_val = $data->__toArray())) {
                        $val = ($_val[$default] ?? null) ?: ($_val[$_key] ?? null);
                    }
                }
            } elseif (\is_array($data)) {
                if (\is_string($default) || \is_numeric($default)) {
                    $val = $data[$default] ?? null;
                }
                if (\is_null($val)) {
                    $val = $data[$_key] ?? null;
                }
            }

            if (\is_null($val) && $callback) {
                $val = $callback($key);
            }

            $result[$key] = $val;
        }

        return $result;
    }

    public static function enbase64(string $text, bool $urlsafe = false) : string
    {
        if ($urlsafe) {
            return \rtrim(\strtr(\base64_encode($text), '+/', '-_'), '=');
        }

        return \base64_encode($text);
    }

    public static function debase64(string $base64, bool $urlsafe = false) : string
    {
        if ($urlsafe) {
            return \base64_decode(\str_pad(\strtr($base64, '-_', '+/'), \strlen($base64) % 4, '=', STR_PAD_RIGHT));
        }

        return \base64_decode($base64);
    }

    public static function classname($target, bool $withNS = false) : ?string
    {
        $namespace = null;
        if (IS::namespace($target)) {
            $namespace = $target;
        } elseif (\is_object($target)) {
            $namespace = \get_class($target);
        }

        if (\is_null($namespace)) {
            return null;
        }
        if ($withNS) {
            return $namespace;
        }

        return Arr::last($namespace, '\\');
    }

    public static function path(string $path, string $separator = DIRECTORY_SEPARATOR) : string
    {
        $arr = Str::arr(($path = \trim($path)), $separator, true);
        if (Str::start($separator, $path)) {
            return $separator.\join($separator, $arr);
        }

        return \join($separator, $arr);
    }

    /**
     * Tring to format valid namespace based on an arbitrary string
     *
     * @param string $origin: arbitrary string
     * @param string $separator: separator to separate arbitrary string
     * @param bool $full: true - return full namespace (A\B\C) or false - just parent namespace (A\B)
     * @param bool $root: prefix with `\\` in the result (normal situation only) or not
     */
    public static function namespace(
        string $origin,
        string $separator = '\\',
        bool $full = false,
        bool $root = false
    ) : string {
        $origin = \trim($origin);
        if (IS::empty($origin)) {
            return $full ? '\\' : '';
        }
        if ($origin === '\\') {
            return $full ? '\\' : '';
        }

        $items = \array_map(function ($item) {
            return Format::u2c($item, CASE_UPPER);
        }, Str::arr($origin, $separator));

        $count = \count($items);
        if ($count < 1) {
            return $full ? '\\' : '';
        }
        if (! $full) {
            unset($items[--$count]);
        }
        if ($count < 1) {
            return $full ? '\\' : '';
        }
        if ($root) {
            return '\\'. \join('\\', $items);
        }

        return \join('\\', $items);
    }

    public static function route(string $route, string $separator = '/', bool $join = true)
    {
        $arr = Str::arr($route, $separator);

        if ($join) {
            return empty($arr) ? $separator : \join($separator, $arr);
        }

        return $arr;
    }

    public static function timestamp(int $timestamp = null, string $format = null)
    {
        $timestamp = $timestamp ?? \time();
        $format = $format ?? 'Y-m-d H:i:s';

        return \date($format, $timestamp);
    }

    // public static function seconds($time, int $presision = 4) : string
    // {
    //     $integer = $itme;
    //     $decimal = 0;

            // TODO

    //     if (\is_float($time)) {
    //         $readable = \round($time, $presision);
    //     } elseif (\is_int($time)) {
    //         $readable = $time;
    //     } else {
    //         return '0s';
    //     }

    //     return "{$readable}s";
    // }

    public static function time(string $format = 'Y-m-d H:i:s', $raw = null) : string
    {
        $raw = $raw ? $raw : \time();

        return \date($format, $raw);
    }

    public static function microtime(string $format = 'Y-m-d H:i:s', string $separate = '.', $raw = null) : string
    {
        $raw = $raw ? $raw : \microtime(true);
        $mts = \explode('.', (string) $raw);
        $ts = \intval($mts[0] ?? \time());
        $ms = \intval($mts[1] ?? 0);

        return \join($separate, [\date($format, $ts), $ms]);
    }

    public static function bytes(int $bytes) : string
    {
        $s = ['B', 'Kb', 'MB', 'GB', 'TB', 'PB'];
        $th = \floor(log($bytes)/log(1024));
      
        return \sprintf('%.6f '.$s[$th], ($bytes/pow(1024, \floor($th))));
    }

    public static function interpolate(string $message, array $context = [], string $start = '{', string $end = '}') : string
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if ((! \is_array($val)) && ((! \is_object($val)) || \method_exists($val, '__toString'))) {
                $replace[$start.$key.$end] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return \strtr($message, $replace);
    }

    public static function string($value, bool $throw = true) : ?string
    {
        if (\is_string($value)) {
            return $value;
        }
        if (\is_null($value)) {
            return '';
        }
        if (\is_scalar($value)) {
            return (string) $value;
        }
        if (\is_array($value)) {
            return JSON::encode($value);
        }

        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return Format::string($value->__toString());
            }
            if (\method_exists($value, '__toArray')) {
                return Format::string($value->__toArray());
            }
        }

        if ($throw) {
            throw new FormatExceptor('UNSTRINGIFIABLE_VALUE', ['value' => $value, 'type' => \gettype($value)]);
        }

        return '';
    }

    public static function u2c(string $str, int $first = null) : string
    {
        $arr = \str_split($str);
        $len = \count($arr);

        if ('_' != $arr[0]) {
            $arr[0] = \strtoupper($arr[0]);
        }

        foreach ($arr as $key => $val) {
            if ('_' == $val) {
                if (($key < ($len-1))
                    && ('_' != $arr[$key+1])
                ) {
                    $arr[$key+1] = \strtoupper($arr[$key+1]);
                }
                $arr[$key] = '';
            }
            // $camelcase .= $arr[$key];    // slower than \implode()
        }

        $res = \implode('', $arr);

        if ($first === CASE_UPPER) {
            return \ucfirst($res);
        } elseif ($first === CASE_LOWER) {
            return \lcfirst($res);
        }

        return $res;
    }

    /**
     * Convert a CamelCase string into underline
     *
     * @param string $str: The string to be converted
     * @return string: The string had been converted
     */
    public static function c2u(string $str, int $case = null) : string
    {
        $arr = \str_split($str);

        foreach ($arr as $key => $val) {
            if (0 == $key) {
                $arr[0] = \strtolower($val);
            } else {
                if (Str::charcase($val, 1)) {
                    $arr[$key] = '_'.\strtolower($val);
                }
            }
        }

        $res = \implode('', $arr);
        if ($case === CASE_UPPER) {
            return \strtoupper($res);
        } elseif ($case === CASE_LOWER) {
            return \strtolower($res);
        }

        return $res;
    }
}
