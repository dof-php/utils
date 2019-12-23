<?php

declare(strict_types=1);

namespace DOF\Util;

use ArrayAccess;
use Countable;
use JsonSerializable;
use DOF\Util\Validator;
use DOF\Util\Exceptor\DictExceptor;

class Dict implements
    ArrayAccess,
    Countable,
    JsonSerializable
{
    const CALLBACK_GET = '__dictGet';

    protected $origin = null;
    protected $data = [];
    protected $keys = [];
    protected $count = 0;

    public function __construct(array $data = [], $origin = null)
    {
        $this->origin = $origin;

        $this->setData($data);
    }

    final public function has(string $key) : bool
    {
        return \array_key_exists($key, $this->data);
    }

    final public function get(string $key, $default = null, array $rules = [])
    {
        $val = $this->data[$key] ?? null;
        if (\is_null($val)) {
            if ($this->origin && \method_exists($this->origin, static::CALLBACK_GET)) {
                $val = \call_user_func_array([$this->origin, static::CALLBACK_GET], [$key, $this]);
            }
        }

        $val = \is_null($val) ? $default : $val;
        if (! $rules) {
            return $val;
        }

        $validator = new Validator;
        $validator->setData([$key => $val])->setRules([$key => $rules])->execute();

        return $validator->getResult()[$key] ?? null;
    }

    final public function append(string $key, $value)
    {
        if (! $key) {
            throw new DictExceptor('INVALID_DICT_KEY', \compact('key'));
        }

        if (! $this->has($key)) {
            $this->keys[] = $key;
        }

        $this->data[$key][] = $value;

        return $this;
    }

    final public function set(string $key, $value)
    {
        if (! $key) {
            throw new DictExceptor('INVALID_DICT_KEY', \compact('key'));
        }

        $this->keys[] = $key;
        $this->data[$key] = $value;

        return $this;
    }

    final public function origin()
    {
        return $this->origin;
    }

    final public function setData(array $data)
    {
        $this->data = $data;
        $this->keys = \array_keys($data);

        return $this;
    }

    final public function getData() : array
    {
        return $this->data;
    }

    final public function keys() : array
    {
        return $this->keys;
    }

    final public function last(string $nameKey = 'key', string $nameVal = 'val')
    {
        $key = $this->keys[$this->count() - 1] ?? null;
        if (IS::empty($key)) {
            return null;
        }

        return [$nameKey => $key, $nameVal => $this->get($key)];
    }

    final public function one(string $nameKey = 'key', string $nameVal = 'val')
    {
        $key = $this->keys[\mt_rand(0, ($this->count() - 1))] ?? null;
        if (IS::empty($key)) {
            return null;
        }

        return [$nameKey => $key, $nameVal => $this->get($key)];
    }

    final public function first(string $nameKey = 'key', string $nameVal = 'val')
    {
        $key = $this->keys[0] ?? null;
        if (IS::empty($key)) {
            return null;
        }

        return [$nameKey => $key, $nameVal => $this->get($key)];
    }

    final public function offsetExists($offset) : bool
    {
        return isset($this->data[$offset]);
    }
    
    final public function offsetGet($offset) : bool
    {
        return $this->get($offset);
    }
    
    final public function offsetSet($offset, $value) : bool
    {
        $this->set($offset, $value);
    }
    
    final public function offsetUnset($offset) : bool
    {
        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
        }
    }
    
    final public function empty() : bool
    {
        return $this->count() === 0;
    }

    final public function count() : int
    {
        return \count($this->data);
    }
   
    final public function jsonSerialize()
    {
        return $this->data;
    }

    final public function __call(string $method, array $argvs)
    {
        if ($this->origin && \method_exists($this->origin, $method)) {
            return \call_user_func_array([$this->origin, $method], $argvs);
        }

        return $this->__get($method);
    }

    final public function __get(string $key)
    {
        return $this->get($key);
    }

    final public function __set($key, $value)
    {
        return $this->set($key, $value);
    }
    
    final public function __toArray()
    {
        return $this->data;
    }
    
    final public function __toString()
    {
        return JSON::encocde($this->data);
    }
}
