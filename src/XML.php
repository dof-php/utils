<?php

declare(strict_types=1);

namespace DOF\Util;

use DOF\Util\Exceptor;
use DOF\Util\Exceptor\FormatExceptor;
use DOF\Util\Exceptor\FSExceptor;

final class XML
{
    public static function arr2xml(array $data, string &$xml) : string
    {
        foreach ($data as $key => &$val) {
            if (\is_array($val)) {
                $_xml = '';
                $val  = XML::arr2xml($val, $_xml);
            }

            $xml .= "<{$key}>{$val}</{$key}>";
        }

        unset($val);

        return $xml;
    }

    public static function encode(array $data, bool $pretty = false, bool $header = true) : string
    {
        $xml = '';

        $_xml = XML::arr2xml($data, $xml);

        unset($xml);

        $__xml = $header ? '<?xml version="1.0" encoding="utf-8"?>' : '';
        $__xml .= '<xml>'.$_xml.'</xml>';

        return $__xml;
    }

    // TODO&&FIXME
    public static function decode(string $xml, bool $loaded = false, bool $file = false)
    {
        if (! \extension_loaded('libxml')) {
            throw new Exceptor('PHP_EXTENSION_MISSING', ['libxml']);
        }

        if ($file) {
            $file = $xml;
            if (!\is_file($file)) {
                throw new FormatExceptor('XML_DECODE_FAILED', [
                    'info' => 'XmlFileNotExists',
                    'file' => $file,
                ]);
            }

            $xml = \file_get_contents($file);
            if (! \is_string($xml)) {
                throw new FSExceptor('FILE_READ_ERROR', [
                    'file' => $file,
                    'info' => $xml,
                ]);
            }

            $loaded = false;
        }
       
        libxml_use_internal_errors(true);

        $xml = $loaded
        ? $xml
        : simplexml_load_string(
            $xml,
            'SimpleXMLElement',
            LIBXML_NOCDATA
        );

        if (($error = libxml_get_last_error()) && isset($error->message)) {
            libxml_clear_errors();

            throw new FormatExceptor('XML_DECODE_FAILED', [
                'xml' => $xml,
                'info' => 'IllegalXMLformat',
                'error' => $error->message,
            ]);
        }

        return JSON::decode(JSON::encode($xml), true);
    }

    public static function pretty(array $data) : string
    {
        // TODO
    }
}
