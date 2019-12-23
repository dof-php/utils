<?php

declare(strict_types=1);

namespace DOF\Util\Wrapper;

class Classic
{
    public function wraperr()
    {
        return ['code', 'info', 'more'];
    }

    public function wrapout()
    {
        return [
            '__DATA__' => 'data',
            'code' => 0,
            '__INFO__' => 'info',
            'more',
        ];
    }
}
