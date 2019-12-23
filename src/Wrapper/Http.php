<?php

declare(strict_types=1);

namespace DOF\Util\Wrapper;

class Http
{
    public function wrapout()
    {
        return [
            '__DATA__' => 'data',
            'status' => 200,
            '__INFO__' =>
            'message',
            'meta',
            'extra'
        ];
    }
}
