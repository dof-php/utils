<?php

declare(strict_types=1);

namespace DOF\Util;

final class Num
{
    // handle big int/float output style
    public static function big($value, int $decimals = -1)
    {
        if (! \is_numeric($value)) {
            return null;
        }

        if ($decimals < 0) {
            return (int) \sprintf('%.0f', $value);
        }

        // TODO&FIXME
        return (float) \sprintf("%.{$decimals}f", $value);
        // return (float) number_format($value, $decimals, '.', '');
    }

    public static function between($num, $start, $end, bool $equal = true) : bool
    {
        if ($equal) {
            return ($start <= $num) && ($num <= $end);
        }

        return ($start < $num) && ($num < $end);
    }
}
