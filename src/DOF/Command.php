<?php

declare(strict_types=1);

namespace DOF\Util\DOF;

use DOF\Util\IS;
use DOF\Util\FS;
use DOF\Util\Rand;
use DOF\Util\JSON;
use DOF\Util\Format;
use DOF\Util\TypeCast;

class Command
{
    /**
     * @CMD(url.e)
     * @Desc(Encode origin URL stirng to encoded URL format)
     * @Option(raw){notes=Use `rawurlencode` instead of `urlencode`}
     * @Argv(1){notes=Origin URL to encode}
     */
    public function urlencode($console)
    {
        if (! ($str = $console->first())) {
            return $console->fail('MissingURLStringToEncode');
        }

        $console->line($console->hasOption('raw') ? \rawurlencode($str) : \urlencode($str));
    }

    /**
     * @CMD(url.d)
     * @Desc(Decode encoded URL to origin URL)
     * @Option(raw){notes=Use `rawurldecode` instead of `urldecode`}
     * @Argv(1){notes=URL encoded string to decode}
     */
    public function urldecode($console)
    {
        if (! ($str = $console->first())) {
            return $console->fail('MissingURLStringToDecode');
        }

        $console->line($console->hasOption('raw') ? \rawurldecode($str) : \urldecode($str));
    }

    /**
     * @CMD(bse)
     * @Desc(Encode string base64 format)
     * @Option(urlsafe){notes=Encode base64 string in URL safe mode&default=false}
     * @Argv(1){notes=Origin string to encode}
     */
    public function base64encode($console)
    {
        if (! ($str = $console->first())) {
            return $console->fail('MissingBase64StringToEncode');
        }

        $console->line(Format::enbase64($str, $console->hasOption('urlsafe')));
    }

    /**
     * @CMD(bsd)
     * @Desc(Decode base64 string)
     * @Option(urlsafe){notes=Decode base64 string in URL safe mode&default=false}
     * @Argv(1){notes=Base64 string to decode}
     */
    public function base64decode($console)
    {
        if (! ($bs = $console->first())) {
            return $console->fail('MissingBase64StringToDecode');
        }

        $console->line(Format::debase64($bs, $console->hasOption('urlsafe')));
    }

    /**
     * @CMD(t2d)
     * @Desc(Convert UNIX timestamp to human readable date string)
     * @Argv(1){notes=Timestamp to convert}
     */
    public function timestamp2date($console)
    {
        if (! ($ts = $console->first())) {
            return $console->fail('MissingTimestampToConvert');
        }
        if (! IS::timestamp($ts)) {
            return $console->fail('InvalidTimestamp', \compact('ts'));
        }

        $console->line(Format::timestamp(TypeCast::int($ts)));
    }

    /**
     * @CMD(d2t)
     * @Desc(Convert date string to UNIX timestamp)
     * @Argv(1){notes=Date string to convert}
     */
    public function date2timestamp($console)
    {
        if (! ($date = $console->first())) {
            return $console->fail('MissingDateToConvert');
        }

        $console->line(\strtotime($date));
    }

    /**
     * @CMD(ns)
     * @Desc(Get namespace of php file, or get php filepath of namespace)
     * @Option(full){notes=Print full namespace/filepath or not; default: TRUE - get namespace, FALSE - get filepath}
     * @Argv(1){notes=File path of Class/Interface/Trait, or namespace}
     */
    public function getNamespaceOfFile($console)
    {
        $target = $console->first();
        if (! $target) {
            $console->fail('MissingTarget');
        }
        if (IS::namespace($target)) {
            $file = Reflect::getNamespaceFile($target);
            $file = ($file && (! $console->hasOption('full')))
                ? DMN::path($file)
                : $file;
            $file ? $console->success($file) : $console->warn('nil');
            return;
        }

        $ns = Reflect::getFileNamespace($target, \boolval($console->getOption('full', true)));
        $ns ? $console->success($ns) : $console->warn('nil');
    }

    /**
     * @CMD(rand)
     * @Desc(Get an ASCII based random data)
     * @Argv(1){notes=Length of random data}
     * @Option(length){notes=Length limit of rand data&default=random}
     * @Option(mode){notes=Compositions of rand data: 1-NUM, 2-AUP, 4-ALO, 7-STR, 8-PUN, 6-CHR, 15-ALL&default=STR}
     * @Option(exclude){notes=Exclude default special punctuation marks in result&default=true}
     * @Option(excludes){notes=Characters you want to exclude in result&default=null}
     */
    public function rand($console)
    {
        $length = \intval($console->first($console->getOption('length', \mt_rand(2, 16))));
        $mode = $console->getOption('mode', Rand::STR);
        if (! \in_array($mode, [Rand::NUM, Rand::AUP, Rand::ALO, Rand::STR, Rand::PUN, Rand::CHR, Rand::ALL])) {
            $mode = Rand::STR;
        }

        $excludes = $console->getOption('excludes');
        if (! $excludes) {
            $excludes = $console->getOption('exclude', true) ? Rand::EXCLUDE : null;
        }

        $console->line(Rand::ascii($length, \intval($mode), $excludes));
    }

    /**
     * @CMD(u2c)
     * @Desc(Convert a underline string to camelcase)
     */
    public function u2c($console)
    {
        $str = $console->first();
        if ($str) {
            $console->line(Format::u2c($str));
        }
    }

    /**
     * @CMD(c2u)
     * @Desc(Convert a camelcase string to underline)
     */
    public function c2u($console)
    {
        $str = $console->first();
        if ($str) {
            $console->line(Format::c2u($str));
        }
    }
    /**
     * @CMD(format.bytes)
     * @Desc(Format bytes to human readable string)
     * @Argv(1){notes=Bytes integer}
     */
    public function formatBytes($console)
    {
        $console->line(\intval($console->first()));
    }
}
