<?php

declare(strict_types=1);

namespace DOF\Util;

use ArrayIterator;
use IteratorAggregate;

class Collection extends Dict implements IteratorAggregate
{
    final public function getIterator()
    {
        return new ArrayIterator($this);
    }
}
