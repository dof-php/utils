<?php

declare(strict_types=1);

namespace DOF\Util\Wrapper;

class ActionOnly
{
    public function wraperr()
    {
        return ['code', 'info', 'more'];
    }

    public function wrapout()
    {
        return ['code' => 0, '__INFO__' => 'info', 'more'];
    }
}
