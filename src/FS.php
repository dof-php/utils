<?php

declare(strict_types=1);

namespace DOF\Util;

use Closure;
use FilesystemIterator;
use DOF\Util\Exceptor\FSExceptor;

/**
 * Common operations of file system
 */
final class FS
{
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Copy dir or file in a jiffy
     */
    public static function copy(string $from, string $to, bool $strict = true)
    {
        if (\is_file($from)) {
            if (\is_file($to)) {
                if ($strict) {
                    throw new FSExceptor('COPY_DESTINATION_EXISTS', \compact('to'));
                }
                return;
            }
            if (\is_dir($to)) {
                if (false === copy($from, FS::path($to, \basename($from)))) {
                    if ($strict) {
                        throw new FSExceptor('COPY_FAILED_1', \compact('from', 'to'));
                    }
                }
                return;
            }
            if (Str::end(FS::DS, $to)) {
                if (false === FS::mkdir($to)) {
                    if ($strict) {
                        throw new FSExceptor('COPY_DESTINATION_UNWRITABLE', \compact('to'));
                    }
                    return;
                }
                $to = FS::path($to, \basename($from));
            }
            if (false === FS::mkdir(\dirname($to))) {
                if ($strict) {
                    throw new FSExceptor('COPY_DIST_DIR_UNWRITABLE', \compact('to'));
                }
            }
            if (false === copy($from, $to)) {
                if ($strict) {
                    throw new FSExceptor('COPY_FAILED_2', \compact('from', 'to'));
                }
            }
            return;
        }

        if (\is_dir($from)) {
            // TODO&FIXME
            return;
        }

        if ($strict) {
            throw new FSExceptor('INVALID_COPY_SOURCE', \compact('from'));
        }
    }

    public static function ls(Closure $callback, ...$items)
    {
        $dir = FS::path(...$items);
        if (! \is_dir($dir)) {
            throw new FSExceptor('DIRECTORY_NOT_EXISTS', \compact('dir'));
        }

        $list = (array) \scandir($dir, 0);    // ascii asc
        // ignore meta dirs `.` and `..` as default
        $list = \array_slice($list, 2);

        $callback($list, $dir);
    }

    public static function ll(string $dir, Closure $callback, bool $meta = false)
    {
        if (! \is_dir($dir)) {
            throw new FSExceptor('DIRECTORY_NOT_EXISTS', \compact('dir'));
        }

        $list = (array) \scandir($dir, 0);    // ascii asc
        // $list = (array) \scandir($dir, SCANDIR_SORT_NONE);    // disable sort rule

        if (! $meta) {
            $list = \array_slice($list, 2);
        }

        $callback($list, $dir);
    }

    public static function walk(string $dir, Closure $callback)
    {
        if (! \is_dir($dir)) {
            throw new FSExceptor('DIRECTORY_NOT_EXISTS', \compact('dir'));
        }

        $fsi = new FilesystemIterator($dir);
        foreach ($fsi as $path) {
            $callback($path);
        }

        unset($fsi);
    }

    public static function walkr(string $dir, Closure $callback)
    {
        FS::walk($dir, function ($path) use ($callback) {
            if (\in_array($path->getFileName(), ['.', '..'])) {
                return;
            }
            if ($path->isDir()) {
                FS::walkr($path->getRealpath(), $callback, true);
                return;
            }

            $callback($path);
        });
    }

    public static function path(...$items) : string
    {
        $path = '';

        FS::__path($items, $path);

        return $path;
    }

    private static function __path(array $items, string &$path = '')
    {
        foreach ($items as $item) {
            if (\is_scalar($item)) {
                $item = (string) $item;
                $join = ($path === '') ? [$item] : [$path, $item];
                $path = \join(DIRECTORY_SEPARATOR, $join);
                continue;
            }
            if (\is_array($item)) {
                self::__path($item, $path);
                continue;
            }
        }
    }

    public static function read(string $file)
    {
        if (!\is_file($file)) {
            throw new FSExceptor('READ_FILE_NOT_EXISTS', \compact('file'));
        }

        return \file_get_contents($file);
    }
    
    public static function render(array $data, string $template, string $dist = null, Closure $exception = null)
    {
        if (! \is_file($template)) {
            throw new FSExceptor('RENDER_TEMPLATE_NOT_EXISTS', \compact('template'));
        }

        $content = Str::buffer(function () use ($data, $template) {
            \extract($data, EXTR_OVERWRITE);

            include $template;
        }, $exception);
        
        if ($dist) {
            FS::save($dist, $content);
        }
    }

    public static function save(string $path, string $content, bool $append = false)
    {
        $dir = \dirname($path);

        if (false === FS::mkdir($dir)) {
            throw new FSExceptor('MKDIR_FAILED', \compact('dir'));
        }

        \file_put_contents($path, $content, ($append ? FILE_APPEND : 0));
    }

    public static function mkdir(...$items)
    {
        $path = FS::path(...$items);

        if (\is_dir($path)) {
            return $path;
        }

        if (@mkdir($path, 0755, true)) {
            return $path;
        }

        return false;
    }

    public static function rmdir(string $path, array $excludes = [])
    {
        if (! \is_dir($path)) {
            return;
        }

        FS::ll($path, function (array $list, string $dir) use ($excludes) {
            foreach ($list as $file) {
                if ($file === '.' || '..' === $file) {
                    continue;
                }
                if (\in_array($file, $excludes)) {
                    continue;
                }

                FS::unlink($dir, $file);
            }
        });
    }

    public static function unlink(...$items)
    {
        $path = FS::path(...$items);

        if (\is_file($path)) {
            unlink($path);
        }

        if (\is_dir($path)) {
            FS::ls(function (array $list, string $dir) {
                foreach ($list as $file) {
                    FS::unlink($dir, $file);
                }
            }, $path);

            rmdir($path);
        }

        return $path;
    }
}
