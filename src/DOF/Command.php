<?php

declare(strict_types=1);

namespace DOF\Util\DOF;

use DOF\Util\IS;
use DOF\Util\FS;
use DOF\Util\Rand;
use DOF\Util\JSON;

class Command
{
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
