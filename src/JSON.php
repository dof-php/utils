<?php

declare(strict_types=1);

namespace DOF\Util;

use DOF\Util\FormatExceptor;

final class JSON
{
    public static function encode($data, bool $pretty = false, int $depth = 512) : string
    {
        $option = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if ($pretty) {
            $option |= JSON_PRETTY_PRINT;
        }

        $json = \json_encode($data, $option, $depth);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        $error = json_last_error_msg() ?: 'UnknownJsonEncodeError';

        throw new FormatExceptor('JSON_ENCODE_ERROR', \compact('error'));
    }

    public static function decode(string $json, bool $assoc = true, bool $file = false)
    {
        if ($file) {
            $file = $json;
            if (!\is_file($file)) {
                throw new FormatExceptor('JSON_DECODE_FAILED', [
                    'info' => 'JsonFileNotExists',
                    'file' => $file,
                ]);
            }

            $json = \file_get_contents($file);
            if (! \is_string($json)) {
                throw new FormatExceptor('FILE_READ_ERROR', [
                    'file' => $file,
                    'info' => $json,
                ]);
            }
        }

        $result = \json_decode($json, $assoc);

        if (\is_array($result) || \is_object($result)) {
            return $result;
        }

        throw new FormatExceptor('JSON_DECODE_FAILED', \compact('json', 'result'));
    }

    public static function pretty($data) : string
    {
        return JSON::encode($data, true);
    }
}
