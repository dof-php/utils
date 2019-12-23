<?php

declare(strict_types=1);

namespace DOF\Util\Surrogate;

use DOF\Util\Surrogate;
use DOF\Util\Validator as Instance;

final class Validator extends Surrogate
{
    public static function namespace() : string
    {
        return Instance::class;
    }
}
