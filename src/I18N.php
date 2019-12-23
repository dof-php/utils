<?php

declare(strict_types=1);

namespace DOF\Util;

use DOF\Util\FS;
use DOF\Util\Arr;
use DOF\Util\Format;

class I18N
{
    private static $lang = [];

    public static function lang(string $lang) : string
    {
        // You can override this code to customize your language assets path
        return FS::path(\dirname(__DIR__), 'lang', \join('.', [$lang, 'php']));
    }

    /**
     * Get a translation result by given i18n key and language code
     *
     * @param $key: i18n key
     * @param $lang: language code, see more: https://i18ns.com/languagecode.html
     */
    final public static function get(string $key, string $lang = 'en', array $context = []) : string
    {
        $path = static::lang($lang);

        if ($map = (self::$lang[$path] ?? null)) {
        } else {
            $map = self::$lang[$path] = Arr::load($path);
        }

        if (empty($map)) {
            return $key;
        }

        if ($item = ($map[$key] ?? null)) {
            return Format::interpolate($item, $context);
        }

        return $key;
    }
}
