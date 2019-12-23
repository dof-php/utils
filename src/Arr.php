<?php

declare(strict_types=1);

namespace DOF\Util;

use Throwable;
use Closure;
use DOF\Util\IS;
use DOF\Util\Str;
use DOF\Util\Format;
use DOF\Util\Exceptor\FSExceptor;

class Arr
{
    const INDEX = 'index';
    const ASSOC = 'assoc';

    // Desensitize sensitive array items
    public static function mask(
        array $data,
        array $keys = ['password', 'queue', 'passwd', 'pswd', 'secret'],
        string $mask = '*'
    ) : array {
        foreach ($data as $key => &$value) {
            if (\is_array($value)) {
                $value = static::mask($value, $keys, $mask);
                continue;
            }
            if (\is_string($key) && $keys && IS::ciin($key, $keys)) {
                $value = $mask;
            }
        }

        return $data;
    }

    public static function remove(array $data, array $keys, bool $reserveKeys = true) : array
    {
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $reserveKeys ? $data : \array_values($data);
    }

    public static function unset(array &$data, array $keys, bool $reserveKeys = true) : void
    {
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        if (! $reserveKeys) {
            $data = \array_values($data);
        }
    }

    public static function id(array $ids = null, bool $unique = false) : array
    {
        if (! $ids) {
            return [];
        }

        $_ids = [];
        foreach ($ids as $id) {
            if (TypeHint::pint($id)) {
                $_ids[] = \intval($id);
            }
        }

        return $unique ? \array_unique($_ids) : $_ids;
    }

    /**
     * Match multiple keys in given data and return matched key
     * @param array $keys: keys to match, index array
     * @param array $data: haystack, assoc array
     * @param mixed $default: default value when match failed
     * @param string $_key: the key first been matched
     */
    public static function match(array $keys, array $data = null, $default = null, string &$_key = null)
    {
        if ($data) {
            foreach ($keys as $key) {
                if (\is_string($key) && (! \is_null($val = $data[$key] ?? null))) {
                    $_key = $key;
                    return $val;
                }
            }
        }

        return $default;
    }

    public static function cast($value) : array
    {
        if (IS::empty($value)) {
            return [];
        }

        return \is_array($value) ? $value : [$value];
    }

    public static function str(array $arr, string $implode) : string
    {
        return \join($implode, static::trim($arr));
    }

    /**
     * Unique merges one-dimension array only
     */
    public static function union(...$arrays) : array
    {
        $result = \array_merge(...$arrays);
        if (! \is_array($result)) {
            return [];
        }

        return \array_values(\array_unique($result));
    }

    /**
     * Check if two arrays are equal
     *
     * - have the same key/value pairs in the same order and of the same types.
     * - have the same key/value pairs only.
     *
     * @param array $compare
     * @param array $to
     * @param bool $withOrder
     * @return bool
     */
    public static function eq(array $compare, array $to, bool $withOrder = true) : bool
    {
        return static::equal($compare, $to, $withOrder);
    }

    public static function equal(array $compare, array $to, bool $withOrder = true) : bool
    {
        if ($withOrder) {
            return $compare === $to;
        }

        IS::array($compare, Arr::ASSOC) ? \ksort($compare) : \sort($compare);
        IS::array($to, Arr::ASSOC) ? \ksort($to) : \sort($to);

        return $compare == $to;
    }

    public static function load(string $file, bool $strict = true) : array
    {
        if (!\is_file($file)) {
            if ($strict) {
                throw new FSExceptor('ARR_FILE_NOT_EXISTS', \compact('file'));
            }
            return [];
        }
        if (Str::end('.json', $file)) {
            return JSON::decode($file, true, true);
        }

        // reard as php file as default
        try {
            $result = include $file;
            if ((1 === $result) || (! \is_array($result))) {
                return [];
            }

            return $result;
        } catch (Throwable $th) {
            if ($strict) {
                throw new FSExceptor('LOAD_PHP_FILE_ERROR', \compact('file'), $th);
            }
            return [];
        }

        return [];
    }

    public static function save(array $data, string $path, bool $strip = true)
    {
        $code = static::code($data);
        $code = <<<ARR
<?php return {$code};
ARR;
        FS::save($path, $code);
        if ($strip) {
            FS::save($path, php_strip_whitespace($path));
        }
    }

    public static function code(array $arr) : string
    {
        $level = 1;

        $str = "[\n";
        $str .= static::__code($arr, $level);
        $str .= ']';

        unset($level);

        return $str;
    }

    private static function __code(array $arr, &$level) : string
    {
        $str = '';
        $margin = \str_repeat("\t", $level++);
        foreach ($arr as $key => $val) {
            $key  = \is_int($key) ? $key : "'{$key}'";
            $str .= $margin.$key.' => ';
            if (\is_array($val)) {
                $str .= "[\n";
                $str .= static::__code($val, $level);
                $str .= $margin."],\n";
                --$level;
            } else {
                if (\is_string($val)) {
                    $val = "'".\addslashes($val)."'";
                } elseif (\is_numeric($val)) {
                    $val = $val;
                } elseif (\is_bool($val)) {
                    $val = $val ? 'true' : 'false';
                } elseif (\is_null($val)) {
                    $val = 'null';
                } else {
                    $val = "'".\addslashes(Format::string($val))."'";
                }

                $str .= $val.",\n";
            }
        }

        return $str;
    }

    /**
     * Trim empty string, empty sub-array and null items in an array
     *
     * @param array $arr
     * @param bool $perserveKeys
     * @return array
     */
    public static function trim(array $arr, bool $preserveKeys = false) : array
    {
        $res = [];
        
        \array_walk($arr, function ($val, $key) use (&$res, $preserveKeys) {
            if (\is_string($val)) {
                $val = \trim($val);
            }
            if (IS::empty($val)) {
                return;
            }
            if (\is_array($val)) {
                $_val = static::trim($val, $preserveKeys);
                if (! IS::empty($_val)) {
                    $res[$key] = $_val;
                }
                return;
            }

            $res[$key] = $val;
        }, ARRAY_FILTER_USE_BOTH);

        return $preserveKeys ? $res : \array_values($res);
    }

    public static function first($data, string $separator = null)
    {
        if (\is_array($data)) {
            return $data[0] ?? null;
        }
        if (\is_string($data) && $separator) {
            return Str::arr($data, $separator, false)[0] ?? null;
        }
        if (\is_object($data) && \method_exists($data, '__toArray')) {
            return static::first($data->__toArray());
        }

        return null;
    }

    public static function last($data, string $separator = null)
    {
        if (\is_array($data)) {
            return $data[\count($data) - 1] ?? null;
        }
        if (\is_string($data) && $separator) {
            $arr = Str::arr($data, $separator, false);
            return $arr[\count($arr) - 1] ?? null;
        }
        if (\is_object($data) && \method_exists($data, '__toArray')) {
            return static::last($data->__toArray());
        }

        return null;
    }

    public static function partition(array $nodes, string $key, string &$match = null)
    {
        $length = \count($nodes);
        if ($length === 1) {
            return $nodes[$match = (\array_keys($nodes)[0] ?? null)];
        }

        if (IS::array($nodes, 'index')) {
            \sort($nodes);
        } elseif (IS::array($nodes, 'assoc')) {
            \ksort($nodes);
        }

        return $nodes[$match = (\array_keys($nodes)[(Str::partition($key) % \count($nodes))])];
    }

    public static function get(
        string $key,
        array $data,
        $default = null,
        string $explode = '.'
    ) {
        if ((! $key) || (! $data)) {
            return $default;
        }

        if (\array_key_exists($key, $data)) {
            return $data[$key] ?? $default;
        }

        $chain = $_chain = Str::arr($key, $explode);
        $query = null;
        $_data = $data;
        foreach ($chain as $idx => $k) {
            $query = ($_data = ($_data[$k] ?? null));
            if ($query) {
                unset($_chain[$idx]);
                $key = \join($explode, $_chain);
                $val = static::get($key, $query, null, $explode);
                if (! \is_null($val)) {
                    return $val;
                }
            }
        }

        return \is_null($query) ? $default : $query;
    }

    /**
     * See: <https://stackoverflow.com/questions/6092781/finding-the-subsets-of-an-array-in-php>
     */
    public static function subsets(array $data, int $min = 1) : array
    {
        $result = [];

        $count = \count($data);
        $times = pow(2, $count);

        for ($i = 0; $i < $times; ++$i) {
            // $bin = \sprintf('%0'.$count.'b', $i);
            $tmp = [];
            for ($j = 0; $j < $count; ++$j) {
                // Use bitwise operation is more faster than sprintf
                if ($i >> $j & 1) {
                    // if ('1' == $bin{$j}) {    // get NO.$j letter in string $bin
                    $tmp[$j] = $data[$j];
                }
            }
            if (\count($tmp) >= $min) {
                $result[] = $tmp;
            }
        }

        return $result;
    }
}
