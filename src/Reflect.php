<?php

declare(strict_types=1);

namespace DOF\Util;

use Reflection;
use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionException;

final class Reflect
{
    /**
     * Get namespace in annotation
     */
    public static function getAnnotationNamespace(string $annotation, string $origin)
    {
        if (IS::namespace($annotation)) {
            return Format::namespace($annotation, '\\', true);
        }

        if (Str::end('::class', $annotation)) {
            $annotation = Str::shift($annotation, '::class', true);
        }

        if (IS::namespace($annotation)) {
            return Format::namespace($annotation, '\\', true);
        }

        $uses = Reflect::getUsedNamespaces($origin);
        if ($uses) {
            foreach ($uses as $ns => $alias) {
                if ($alias === $annotation) {
                    return $ns;
                }
                $_ns = \explode('\\', $ns);
                $short = $_ns[\count($_ns) - 1];
                if ($short === $annotation) {
                    return $ns;
                }
            }
        }

        // Last try from same namespace of origin
        $_origin = Str::arr($origin, '\\');
        $_origin[\count($_origin) - 1] = $annotation;

        $samens = \join('\\', $_origin);
        if (IS::namespace($samens)) {
            return $samens;
        }

        return null;
    }

    /**
     * Get classes used by class or interface
     *
     * @param string $target
     * @param bool $namespace: if target is a namespace
     * @return array|null
     */
    public static function getUsedNamespaces(string $target, bool $namespace = true) : ?array
    {
        if ($namespace && (! ($target = Reflect::getNamespaceFile($target)))) {
            return null;
        }
        if (!\is_file($target)) {
            return null;
        }

        $tokens = token_get_all(php_strip_whitespace($target));
        $usedClasses = [];
        $foundNamespace = false;
        $findingUsedClass = false;
        $findingAlias = false;
        $usedClass = [];
        foreach ($tokens as $token) {
            $tokenId = $token[0] ?? false;
            $hasNamespace = $tokenId === T_NAMESPACE;
            if ((! $foundNamespace) && (! $hasNamespace)) {
                continue;
            } else {
                $foundNamespace = true;
            }

            $foundClassname = $tokenId === T_CLASS;
            if ($foundClassname) {
                break;
            }
            if ($tokenId === T_USE) {
                $findingUsedClass = true;
                $findingAlias = false;
                continue;
            }
            if ($findingUsedClass) {
                if ($token === ';') {
                    $findingUsedClass = false;
                    if ($usedClass) {
                        $ns = \join('', $usedClass['nspath']);
                        $alias = $usedClass['alias'] ?? null;
                        $usedClasses[$ns] = $alias;
                        $usedClass = [];
                    }
                    continue;
                }
                if (($tokenId !== T_WHITESPACE) && ($tokenName = ($token[1] ?? false))) {
                    if ($tokenId === T_AS) {
                        $findingAlias = true;
                        continue;
                    }

                    if ($findingAlias) {
                        $usedClass['alias'] = $tokenName;
                    } else {
                        $usedClass['nspath'][] = $tokenName;
                    }
                }
            }
        }

        return $usedClasses;
    }

    /**
     * Get filepath of a namespace
     */
    public static function getNamespaceFile(string $namespace)
    {
        if (! IS::namespace($namespace)) {
            return false;
        }

        return (new ReflectionClass($namespace))->getFileName();
    }

    /**
     * Get namespace of a PHP file
     *
     * @param string $path: Path of php file
     * @param bool $withClass: Return result with class name or not
     * @return mixed: string when success or false on failure
     */
    public static function getFileNamespace(string $path, bool $withClass = false)
    {
        if (!\is_file($path)) {
            return false;
        }

        $tokens = token_get_all(php_strip_whitespace($path));
        $cnt = \count($tokens);
        $ns  = $cn = '';
        $nsIdx = $cnIdx = 0;
        $findingNS = $findingCN = true;
        for ($i = 0; $i < $cnt; ++$i) {
            $token = $tokens[$i] ?? false;
            $tname = $token[0] ?? false;
            if ($findingNS && ($tname === T_NAMESPACE)) {
                $nsIdx = $i;
                $findingNS = false;
                continue;
            }
            if ($findingCN && \in_array($tname, [T_CLASS, T_INTERFACE, T_TRAIT])) {
                $cnIdx = $i;
                $findingCN = false;
                continue;
            }
        }
        if ($findingNS === false) {
            for ($j = $nsIdx; $j < $cnt; ++$j) {
                $token = $tokens[$j + 1] ?? false;
                if ($token === ';') {
                    break;
                }
                $ns .= ($token[1] ?? '');
            }
        }
        $ns = \trim($ns);
        if (! $ns) {
            return false;
        }
        if (! $withClass) {
            return $ns ?: '\\';
        }
        $cnLine = [];
        if ($findingCN === false) {
            for ($k = $cnIdx; $k < $cnt; ++$k) {
                $token = $tokens[$k + 1] ?? false;
                if ($token === '{') {
                    break;
                }
                $cnLine[] = ($token[1] ?? '');
            }
        }
        $cnLine = \array_values(\array_filter($cnLine, function ($item) {
            return ! empty(\trim($item));
        }));
        $cn = $cnLine[0] ?? '';
        if (! $cn) {
            return false;
        }
        $cn = \join('\\', [$ns, $cn]);

        return (\class_exists($cn) || interface_exists($cn) || trait_exists($cn)) ? $cn : false;
    }

    public static function getObjectName($object, bool $full = false) : ?string
    {
        if (! \is_object($object)) {
            return null;
        }

        $reflect = new ReflectionClass($object);

        return $full ? $reflect->getName() : $reflect->getShortName();
    }

    public static function getClassConsts(string $class) : ?array
    {
        if (! \class_exists($class)) {
            return null;
        }

        return (new ReflectionClass($class))->getConstants();
    }

    public static function parseInterface(string $interface) : ?array
    {
        try {
            return Reflect::formatInterface(new ReflectionClass($interface));
        } catch (ReflectionException $th) {
            return null;
        }
     }

    public static function parseClass(string $class) : ?array
    {
        try {
            return Reflect::formatClass(new ReflectionClass($class));
        } catch (ReflectionException $th) {
            return null;
        }
    }

    public static function parseClassMethod(string $class, string $method) : ?array
    {
        try {
            $reflector = new ReflectionClass($class);

            return Reflect::formatClassMethod($reflector->getMethod($method));
        } catch (ReflectionException $th) {
            return null;
        }
    }

    public static function parseClassConstructor(string $namespace) : ?array
    {
        return Reflect::parseClassMethod($namespace, '__construct');
    }

    public static function parseClassProperty(string $class, string $property) : ?array
    {
        try {
            $reflector = new ReflectionClass($class);

            return Reflect::formatClassProperty($reflector->getProperty($property));
        } catch (ReflectionException $th) {
            return null;
        }
    }

    public static function formatInterface(ReflectionClass $class) : array
    {
        return [
            'namespace' => $class->getName(),
            'doc' => (string) $class->getDocComment(),
            'methods' => $class->getMethods(),
        ];
    }

    public static function formatClass(ReflectionClass $class) : array
    {
        return [
            'namespace' => $class->getName(),
            'doc' => (string) $class->getDocComment(),
            'properties' => $class->getProperties(),
            'methods' => $class->getMethods(),
            'consts' => $class->getConstants(),
        ];
    }

    public static function formatClassProperty(ReflectionProperty $property) : array
    {
        return [
            'name' => $property->name,
            'reflect' => $property->class,
            'declar' => $property->getDeclaringClass()->name ?? null,
            'doc' => (string) $property->getDocComment(),
            'modifiers' => Reflection::getModifierNames($property->getModifiers()),
        ];
    }

    public static function formatClassMethod(ReflectionMethod $method) : array
    {
        $res = [
            'name' => $method->name,
            'reflect' => $method->class,
            'declar' => $method->getDeclaringClass()->name ?? null,
            'doc' => (string) $method->getDocComment(),
            'file' => $method->getFileName(),
            'line' => $method->getStartLine(),
            'modifiers' => Reflection::getModifierNames($method->getModifiers()),
        ];

        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $res['parameters'][] = Reflect::formatClassMethodParameter($parameter);
        }

        return $res;
    }

    public static function formatClassMethodParameter(ReflectionParameter $parameter) : array
    {
        $type = $parameter->hasType() ? $parameter->getType()->getName() : null;
        $builtin    = $type ? $parameter->getType()->isBuiltin() : null;
        $hasDefault = $parameter->isDefaultValueAvailable();
        $defaultVal = $hasDefault ? $parameter->getDefaultValue() : null;

        return [
            'name' => $parameter->getName(),
            'type' => $type,
            'builtin'  => $builtin,
            'nullable' => $parameter->allowsNull(),
            'optional' => $parameter->isOptional(),
            'default'  => [
                'status' => $hasDefault,
                'value'  => $defaultVal,
            ]
        ];
    }
}
