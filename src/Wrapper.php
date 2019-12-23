<?php

declare(strict_types=1);

namespace DOF\Util;

use Closure;

final class Wrapper
{
    const WRAPERR = 'wraperr';
    const WRAPOUT = 'wrapout';

    public static function err(string $wraperr = null)
    {
        if (IS::empty($wraperr)) {
            return null;
        }

        return Singleton::get($wraperr)->{Wrapper::WRAPERR}();
    }

    public static function out(string $wrapout = null)
    {
        if (IS::empty($wrapout)) {
            return null;
        }

        return Singleton::get($wrapout)->{Wrapper::WRAPOUT}();
    }
}
