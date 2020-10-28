<?php

declare(strict_types=1);

namespace DOF\Util;

use Closure;
use DOF\Util\IS;
use DOF\Util\Exceptor\AnnotationExceptor;

class Annotation
{
    const REGEX = '#@([a-zA-Z\d]+)\((.*)\)(\{(.*)\})?#';
    const CALLBACK_VALUE = '__annotationValue';
    const CALLBACK_EXTRA = '__annotationExtra';
    const EXTRA_KEY = '__EXT';

    /** @var array: Annotations result cache of class or interface */
    private static $results = [];

    /** @var array: Cache of reflection result of file and it's namespace */
    private static $file2ns = [];

    /**
     * Get annotations of class or interface by namespace
     *
     * @param string $filepath
     * @param string $origin
     * @param bool $cache
     * @return array
     */
    public static function getByFilepath(string $filepath, string $origin = null, bool $cache = true) : array
    {
        $namespace = self::$file2ns[$filepath] ?? null;
        if (! $namespace) {
            $namespace = Reflect::getFileNamespace($filepath, true);
        }

        return Annotation::getByNamespace($namespace, $origin, $cache);
    }

    /**
     * Get annotations of class or interface by namespace
     *
     * @param string $namespace
     * @param string $origin
     * @param bool $cache
     * @return array
     */
    public static function getByNamespace(string $namespace, string $origin = null, bool $cache = true) : array
    {
        if (! $namespace) {
            return [];
        }
        if (! $cache) {
            return Annotation::parseNamespace($namespace, $origin);
        }

        $result = self::$results[$namespace] ?? null;
        if ($result) {
            return $result;
        }

        $filepath = Reflect::getNamespaceFile($namespace);
        if ($filepath) {
            self::$file2ns[$filepath] = $namespace;
        }

        return self::$results[$namespace] = Annotation::parseNamespace($namespace, $origin);
    }

    public static function get(string $target, string $origin = null, bool $file = false, bool $cache = true) : array
    {
        return $file ? self::getByFilepath($target, $origin, $cache) : self::getByNamespace($target, $origin, $cache);
    }

    /**
     * Parse class files or interface files annotations by directory paths
     *
     * @param array $dirs: a list of php class or interface directory paths
     * @param Closure $callback: The callback when parse given file finished
     * @return array: A list of a annotations of class/interface, properties and methods of the whole directories
     */
    public static function parseClassDirs(array $dirs, Closure $callback = null, $origin = null) : array
    {
        $result = [];

        foreach ($dirs as $dir) {
            if ((! \is_string($dir)) || (! \is_dir($dir))) {
                throw new AnnotationExceptor('INVALID_DIRECTORY', \compact('dir'));
            }

            $result[$dir] = Annotation::parseClassDir($dir, $callback, $origin);
        }

        return $result;
    }

    /**
     * Parse annotations by directory path, file path or namespace
     *
     * @param array $list: files, directories or namespaces list
     * @param Closure $callback: The callback when given target parsing is finished
     * @return array: Annotation parse result of all the origins
     */
    public static function parseMixed(array $list, Closure $callback = null, $origin = null) : array
    {
        $result = [];

        foreach ($list as $item) {
            if (\is_array($item)) {
                Annotation::parseMixed($item, $callback, $origin);
                continue;
            }
            if (! \is_string($item)) {
                continue;
            }
            if (IS::namespace($item)) {
                $annotations = Annotation::parseNamespace($item, $origin);
                if ($callback) {
                    $callback($annotations);
                }
                $result[$item] = $annotations;
                continue;
            }
            if (\is_file($item)) {
                $result[$item] = Annotation::parseClassFile($item, $callback, $origin);
                continue;
            }
            if (\is_dir($item)) {
                FS::walk($item, function ($path) use ($callback, $origin, &$result) {
                    $realpath = $path->getRealpath();
                    if ($path->isFile() && ('php' === $path->getExtension())) {
                        $result[$realpath] = Annotation::parseClassFile($realpath, $callback, $origin);
                        return;
                    }
                    if ($path->isDir()) {
                        $result[$realpath] = Annotation::parseClassDir($realpath, $callback, $origin);
                        return;
                    }
                });
                continue;
            }
        }

        return $result;
    }

    /**
     * Parse class files or interface files annotations by directory path
     *
     * @param string $dir: the php class or interface directory path
     * @param Closure $callback: The callback when given target parsing is finished
     * @return array: A list of a annotations of class/interface, properties and methods of the whole directory
     */
    public static function parseClassDir(string $dir, Closure $callback = null, $origin = null) : array
    {
        $result = [];

        FS::walk($dir, function ($path) use ($callback, $origin, &$result) {
            $realpath = $path->getRealpath();
            if ($path->isFile() && ('php' === $path->getExtension())) {
                $result[$realpath] = Annotation::parseClassFile($realpath, $callback, $origin);
                return;
            }
            if ($path->isDir()) {
                $result[$realpath] = Annotation::parseClassDir($realpath, $callback, $origin);
                return;
            }
        });

        return $result;
    }

    /**
     * Parse class file or interface file annotations by filepath
     *
     * @param string $path: the php class or interface file path
     * @param Closure $callback: The callback when parse given file finished
     * @return array: A list of a annotations of class/interface, properties and methods
     */
    public static function parseClassFile(string $filepath, Closure $callback = null, $origin = null)
    {
        $namespace = Reflect::getFileNamespace($filepath, true);
        if (! $namespace) {
            throw new AnnotationExceptor('INVALID_NAMESPACE', \compact('filepath', 'namespace'));
        }

        $annotations = Annotation::parseNamespace($namespace, $origin);
        if ($callback) {
            $callback($annotations);
        }

        return $annotations;
    }

    /**
     * Parse class or interface annotations by namespace
     *
     * @param string $namespace
     * @param mixed:object/string $origin: Object/Class using annotation parsing
     * @return array: A list of annotations of class/interface, properties and methods
     */
    public static function parseNamespace(string $namespace, $origin = null) : array
    {
        $ofClass = Reflect::parseClass($namespace);

        $ofClass['doc'] = Annotation::parseComment(($ofClass['doc'] ?? ''), 'meta', $origin, $namespace);

        $ofProperties = Annotation::parseProperties($ofClass['properties'] ?? [], $namespace, $origin);
        $ofMethods = Annotation::parseMethods($ofClass['methods'] ?? [], $namespace, $origin);

        unset($ofClass['properties'], $ofClass['methods']);

        return [$ofClass, $ofProperties, $ofMethods];
    }

    public static function parseProperties(array $properties, string $namespace, $origin = null) : array
    {
        if (! $properties) {
            return [];
        }

        $result = [];
        foreach ($properties as $property) {
            $res = Reflect::formatClassProperty($property);
            $doc = Annotation::parseComment(($res['doc'] ?? ''), 'property', $origin, $namespace);

            if (\is_null($doc)) {
                continue;
            }

            $res['doc'] = $doc;
            $result[$property->name] = $res;
        }

        return $result;
    }

    public static function parseMethods(array $methods, string $namespace, $origin = null) : array
    {
        if (! $methods) {
            return [];
        }

        $result = [];
        foreach ($methods as $method) {
            $res = Reflect::formatClassMethod($method);
            $doc = Annotation::parseComment(($res['doc'] ?? ''), 'method', $origin, $namespace);

            if (\is_null($doc)) {
                continue;
            }

            $res['doc'] = $doc;
            $result[$method->name] = $res;
        }

        return $result;
    }

    public static function parseComment(
        string $comment,
        string $locate,
        $origin = null,
        string $namespace = null
    ) : ?array {
        if (! $comment) {
            return [];
        }

        $res = [];
        $arr = Str::arr($comment, PHP_EOL);
        foreach ($arr as $line) {
            $matches = [];
            if (1 !== \preg_match(Annotation::REGEX, $line, $matches)) {
                continue;
            }
            // eg: @A(1){a=2} => [0 => @A(1){a=2}, 1 => A, 2 => 1, 3 => {a=2}, 4 => a=2]
            $key = \strtoupper($matches[1] ?? '');    // Annotation key/name
            $val = \trim($matches[2] ?? '');    // Annotation value
            $ext = $matches[4] ?? null;    // Annotation extra parameters
            if (! $key) {
                continue;
            }
            if (Str::start('_', $key)) {
                continue;
            }
            if (($key === 'ANNOTATION') && (! IS::confirm($val))) {
                return null;
            }

            list($val, $ext, $multiple) = Annotation::callback($key, $val, $ext, $origin, $locate, $namespace);

            if (! $multiple) {
                $res[$key] = $val;
                if ($ext) {
                    $res[Annotation::EXTRA_KEY][$key] = $ext;
                }
                continue;
            }

            $value = Arr::cast($res[$key] ?? null);
            $_val = \is_array($val) ? $val : [$val];
            $flip = $multiple === 'flip';
            foreach ($_val as $item) {
                if ((! \is_numeric($item)) && (! \is_string($item))) {
                    if ($flip) {
                        throw new AnnotationExceptor('INVALID_ARRAY_FLIP_ITEM', \compact('item'));
                    }

                    continue;
                }

                if ((! Str::eq($multiple, 'isolate', true)) && $ext) {
                    $res[Annotation::EXTRA_KEY][$key][$item] = $ext;
                }
            }

            if ($flip) {
                // !!! array_flip() can only flip STRING and INTEGER values
                // !!! So annotation filter method MUST NOT return as array
                $val = \array_merge(array_flip(Arr::cast($val)) + $value);
            } elseif ($multiple === 'unique') {
                $val = \array_unique(\array_merge(Arr::cast($val), $value));
            } elseif ($multiple === 'index') {
                // See details in the end of this file
                // Search: COMMENT_OPERATOR_FOR_ARRAY
                $val = $value + Arr::cast($val);
            } elseif ($multiple === 'append') {
                \array_push($value, $val);
                $val = $value;
            // } elseif ($multiple === 'assoc') {
            } else {
                $val = \array_merge(Arr::cast($val), $value);
            }

            $res[$key] = $val;
        }

        return $res;
    }

    public static function parseExtra(string $ext = null) : array
    {
        if (! $ext) {
            return [];
        }

        $ext = \trim($ext);

        $_ext = [];

        \parse_str($ext, $_ext);

        $_ext = \array_change_key_case($_ext, CASE_UPPER);

        return $_ext;
    }

    public static function callback(
        string $key,
        string $value = null,
        string $extra = null,
        $origin = null,
        string $locate = '',
        string $namespace = null
    ) {
        $locate = \ucfirst(\strtolower($locate));
        $multiple = false;
        $strict = false;

        if ($origin) {
            // Callback annotation extra first
            $callback = Annotation::CALLBACK_EXTRA.$locate.$key;
            if (! \method_exists($origin, $callback)) {
                $callback = Annotation::CALLBACK_EXTRA.$key;
                if (! \method_exists($origin, $callback)) {
                    $callback = false;
                }
            }
            if ($callback) {
                $extra = \call_user_func_array(
                    [$origin, $callback],
                    [$extra, $namespace]
                );
            } else {
                $extra = Annotation::parseExtra($extra);
            }

            // Callback annotation value
            $callback = Annotation::CALLBACK_VALUE.$locate.$key;
            if (! \method_exists($origin, $callback)) {
                $callback = Annotation::CALLBACK_VALUE.$key;
                if (! \method_exists($origin, $callback)) {
                    $callback = false;
                }
            }
            if ($callback) {
                $value = \call_user_func_array(
                    [$origin, $callback],
                    [$value, $namespace, &$multiple, &$strict, $extra]
                );
            }
        } else {
            $extra = Annotation::parseExtra($extra);
        }

        if ($strict && IS::empty($value)) {
            throw new AnnotationExceptor('EMPTY_ANNOTATION_VALUE', \compact('namespace', 'locate', 'key', 'value', 'origin'));
        }

        return [$value, $extra, $multiple];
    }
}

/*
 * COMMENT_OPERATOR_FOR_ARRAY
 * See: <https://stackoverflow.com/questions/2140090/operator-for-array-in-php>
 *
    $array1 = ['one',   'two',          'foo' => 'bar'];
    $array2 = ['three', 'four', 'five', 'foo' => 'baz'];

    \print_r($array1 + $array2);

    you will get

    array
    (
        [0] => one   // preserved from $array1 (left-hand array)
        [1] => two   // preserved from $array1 (left-hand array)
        [foo] => bar // preserved from $array1 (left-hand array)
        [2] => five  // added from $array2 (right-hand array)
    )

    so the logic of + is equivalent to the following snippet:

    $union = $array1;

    foreach ($array2 as $key => $value) {
        if (false === \array_key_exists($key, $union)) {
            $union[$key] = $value;
        }
    }
 */
