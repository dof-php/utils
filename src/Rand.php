<?php

declare(strict_types=1);

namespace DOF\Util;

use DOF\Util\Exceptor\RandExceptor;

/**
 * Random data generator based on ASCII characters
 *
 * - invisible control characters: 0~31, 127
 * - invisible normal characters: 32 (space)
 * - visible number characters: 48~57
 * - visible uppercase alphabet characters: 65~90
 * - visible lowercase alphabet characters: 97~122
 * - visible punctuation characters: 33~47, 58~64, 91~96, 123~126
 */
class Rand
{
    const SEED_BUFFER = 512;

    const NUM = 1;
    const AUP = 2;
    const ALO = 4;
    const PUN = 8;

    const CHR = 6;
    const STR = 7;
    const ALL = 15;

    const SRC_NUM = '0123456789';
    const SRC_AUP = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const SRC_ALO = 'abcdefghijklmnopqrstuvwxyz';
    const SRC_PUN = <<<PUN
!"#$%&'()*+,-./:;<=>?@[\]^_`{|}~
PUN;
    const EXCLUDE = <<<PUN
'"(){}[]<>+-/\|:;,`^
PUN;

    public static function ascii(int $length, int $mode = self::STR, ...$excludes) : ?string
    {
        $str = '';

        switch ($mode) {
            case self::NUM:
                $src = self::SRC_NUM;
                break;
            case self::AUP:
                $src = self::SRC_AUP;
                break;
            case self::ALO:
                $src = self::SRC_ALO;
                break;
            case self::PUN:
                $src = self::SRC_PUN;
                break;
            case self::CHR:
                $src = self::SRC_AUP.self::SRC_ALO;
                break;
            case self::ALL:
                $src = self::SRC_NUM.self::SRC_AUP.self::SRC_ALO.self::SRC_PUN;
                break;
            case self::STR:
                $src = self::SRC_NUM.self::SRC_AUP.self::SRC_ALO;
                break;
            default:
                return null;
        }

        if ($excludes) {
            foreach ($excludes as $exclude) {
                if (! \is_string($exclude)) {
                    continue;
                }

                $exclude = \str_split($exclude);
                foreach ($exclude as $char) {
                    $src = \str_replace($char, '', $src);
                }
            }
        }

        $max = \strlen($src) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $src[\mt_rand(0, $max)] ?? '';
        }

        return $str;
    }

    public static function get($source = null, int $max = null)
    {
        if (\is_null($source)) {
            return self::ascii(\random_int(0, 127));
        }
        if (\is_array($source)) {
            $keys = \array_keys($source);

            return $vals[\random_int(0, \count($keys) - 1)];
        }
        if (\is_string($source)) {
            $arr = \str_split($source);
            $len = \count($arr);
            $str = '';
            for ($i=0; $i<$len; ++$i) {
                $str .= $arr[\random_int(0, \count($arr) - 1)];
            }

            return $str;
        }
        if (\is_int($source)) {
            if (\is_null($max)) {
                return \random_int(0, $source);
            }
            if ($source === $max) {
                return $source;
            }
            if ($source < $max) {
                return \random_int($source, $max);
            }

            return \random_int($max, $source);
        }

        throw new RandExceptor('INVALID_RANDOM_SOURCE', \compact('source', 'max'));
    }

    public static function str(...$params)
    {
        return self::ascii(...$params);
    }

    // Generate non-cryptographically-secure values, and should not be used for cryptographic purposes
    // If you need a cryptographically secure value, consider using \random_int(), \random_bytes(), or openssl_random_pseudo_bytes() instead.
    public static function int(int $min = null, int $max = null)
    {
        $min = \is_null($min) ? PHP_INT_MIN : $min;
        $max = \is_null($max) ? PHP_INT_MAX : $max;

        if ($min === $max) {
            return $min;
        }
        if ($min > $max) {
            return \mt_rand($max, $min);
        }

        return \mt_rand($min, $max);
    }

    // Generates cryptographically SECURE pseudo-random integers; slower
    public static function sint(int $min = null, int $max = null)
    {
        $min = \is_null($min) ? PHP_INT_MIN : $min;
        $max = \is_null($max) ? PHP_INT_MAX : $max;

        if ($min === $max) {
            return $min;
        }
        if ($min > $max) {
            return \random_int($max, $min);
        }

        return \random_int($min, $max);
    }

    public static function seed(int $len = 32) : string
    {
        if ($len > self::SEED_BUFFER) {
            $len = self::SEED_BUFFER;
        }

        return \substr(\random_bytes($len), 0, $len);
    }

    // UUID version 1: based on time and node ID (usually machine MAC address)
    // 8-4-4-4-12, 36
    // xxxxxxxx-xxxx-Mxxx-Nxxx-xxxxxxxxxxxx - M: UUID version; N: Version variant
    // see: https://en.wikipedia.org/wiki/Universally_unique_identifier
    public static function uuid1(string $identifier, float $time = null) : string
    {
        // TODO
    }

    // UUID version 2: based on time
    public static function uuid2(float $time = null) : string
    {
        // TODO
    }

    // UUID version 3: based on namespace and user provided identifier for MD5 hashing
    public static function uuid3(string $namespace, string $identifier) : string
    {
        // TODO
    }

    // UUID version 4: based on random
    public static function uuid4() : string
    {
        $seed = self::seed(16);

        // $seed[\random_int(0, 16)] = \chr((\ord($seed[\random_int(0, 16)]) & 0x0f) | 0x40);
        // $seed[\random_int(0, 16)] = \chr((\ord($seed[\random_int(0, 16)]) & 0x3f) | 0x80);
        $seed[6] = \chr((\ord($seed[6]) & 0x3f) | 0x80);
        $seed[8] = \chr((\ord($seed[8]) & 0x3f) | 0x80);

        return \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(bin2hex($seed), 4));
    }

    // UUID version 5: based on namespace for sha1 hashing
    public static function uuid5(string $namespace) : string
    {
        // TODO
    }

    /**
     * Get an unique nonce string by an increment ID
     * Version 1: return step-growth dynamic length string (based on length and negative of ID)
     *
     * @param int $id: a global increment decimal unique integer
     * @param bool|null $ci: case sensitive or not (null | CASE_UPPER | CASE_LOWER)
     * @return string
     */
    public static function incr(int $id, int $cs = null) : string
    {
        $neg = $id < 0;
        $abs = $neg ? \abs($id) : $id;
        $str = (string) $abs;
        $len = \strlen($str);
        $cnt = 0;
        $fix = 0;
        if (($len >= 8) || ($len === 6) || ($len === 4) || (($abs % 100) === 0)) {
        } elseif ($len > 6) {
            $cnt = $len - 6;
            $fix = $neg ? 9 : 8;
        } elseif ($len > 4) {
            $cnt = $len - 4;
            $fix = $neg ? 7 : 6;
        } else {
            $cnt = 4 - $len;
            $fix = $neg ? 5 : 4;
        }

        $seed = self::int(0, $abs);
        $_neg = '';
        if ($cs === CASE_UPPER) {
            $ord1 = [65, 90];
            // remove chars A~F used by hexadecimal
            $ord2 = [71, 90];
            // ord of char: odd => negative; even => positive
            $_neg = $neg ? \chr(65 + self::int(0, 12) * ($neg ? 2 : 1)) : '';
        } elseif ($cs === CASE_LOWER) {
            $ord1 = [97, 122];
            $ord2 = [103, 122];
            $_neg = $neg ? \chr(97 + self::int(0, 12) * ($neg ? 2 : 1)) : '';
        } else {
            $ord1 = (($seed % 2) === 0) ? [65, 90]: [97, 122];
            $ord2 = (($seed % 2) === 0) ? [71, 90]: [103, 122];
            $_neg = $neg ? \chr([self::int(65, 90), self::int(97, 122)][self::int(0, 1)]) : '';
        }

        if ($cnt > 0) {
            $str = $_neg.$str;
            for ($i=$cnt; $i > 0; --$i) {
                $pad = (($seed % 2) === 0) ? STR_PAD_RIGHT : STR_PAD_LEFT;
                $str = \str_pad($str, $fix-$i+1, \chr(self::int(...$ord1)), $pad);
            }
        } elseif ($cnt === 0) {
            // Using hexadecimal format of ID plus two rand prefixes[g-zG-Z] and negative symbol when length of ID is over $max
            // Using 0 to separate hexadecimal and prefixes coz hexadecimal will never start with 0
            $str = \chr(self::int(...$ord2)).$_neg.'0'.\strtoupper(\dechex($abs));
        }

        return $str;
    }

    /**
     * Get an unique string with a fixed length based on PHP \uniqid()
     */
    public static function uniqid(string $prefix = null, int $max = 32, int $cs = null) : string
    {
        if ($max < 24) {
            $max = 24;
        }

        $prefix = \is_null($prefix) ? self::ascii(4, self::STR) : $prefix;

        // uniqid() is based on single machine environment
        // don't recommend to use it without machine NO via first $prefix parameter
        return \str_replace('.', $prefix, \uniqid(self::ascii($max - 26, self::STR), true));
    }

    // Generate fixed length random numberic string based on time
    public static function time(string $mts = null) : string
    {
        $mts = $mts ?? \microtime();
        $arr = \explode(' ', $mts);
        $sec = (int) $arr[1] ?? \time();
        $usc = $arr[0] ?? null;
        if ($usc) {
            $usc = \str_pad(\substr($usc, 2, 6), 6, \strval(self::int(0, 9)), STR_PAD_LEFT);
        } else {
            $usc = self::ascii(6, self::NUM);
        }

        return $usc.\date('yHmids', $sec).self::ascii(4, self::NUM);
    }

    // Generate fixed length random string time
    public static function sn(string $prefix = '', string $suffix = '') : string
    {
        return $prefix.\dechex(\intval(\microtime(true) * 1000000000)).self::ascii(4, self::ALO).$suffix;
    }

    // Generate fixed length of NO based on numbers only
    public static function no(int $appid = 0) : string
    {
        $random = self::sint(1000, 9999);

        $appid  = \str_pad(\mb_substr((string) $appid, 0, 5), 3, '0', STR_PAD_LEFT);

        $runtime = \mb_substr(\strrev((string) \getmypid()), 0, 3);
        $runtime = \str_pad($runtime, 3, self::ascii(1, self::NUM), STR_PAD_RIGHT);

        return \date('ymdHis').\mb_substr(\microtime(), 3, 4).$appid.$runtime.$random;
    }
}
