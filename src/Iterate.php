<?php

declare(strict_types=1);

namespace DOF\Util;

use Iterator;

class Iterate extends Dict implements Iterator
{
    private $pointer = 0;

    final public function current()
    {
        return $this->data[$this->keys[$this->pointer]];
    }
    
    final public function key()
    {
        return $this->keys[$this->pointer];
    }
    
    final public function next()
    {
        ++$this->pointer;
    }
    
    final public function rewind()
    {
        $this->pointer = 0;
    }
    
    final public function valid()
    {
        return \array_key_exists($this->keys[$this->pointer] ?? null, $this->data);
    }
}
