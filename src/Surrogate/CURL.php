<?php

declare(strict_types=1);

namespace DOF\Util\Surrogate;

use DOF\Util\Surrogate;
use DOF\Util\CURL as Instance;

final class CURL extends Surrogate
{
    public static function namespace() : string
    {
        return Instance::class;
    }
}
