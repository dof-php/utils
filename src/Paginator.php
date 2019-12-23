<?php

declare(strict_types=1);

namespace DOF\Util;

class Paginator
{
    const DEFAULT_PAGE_SIZE = 10;

    /** @var array: Current data list */
    private $list = [];

    /** @var int: Total rows */
    private $total = 0;

    /** @var int: Current list length */
    private $count = 0;

    /** @var int: Current page number */
    private $page = 1;

    /** @var int: Current page size */
    private $size = 10;

    public function __construct(array $list = [], array $params = [])
    {
        $this->setList($list);
        $this->setParams($params);
    }

    public function getList(): array
    {
        return $this->list;
    }
    
    public function setList(array $list)
    {
        $this->list = $list;
        $this->count = \count($list);
    
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
    
    public function setTotal(int $total)
    {
        $this->total = $total;
    
        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getPage(): int
    {
        return $this->page;
    }
    
    public function setPage(int $page)
    {
        $this->page = $page;
    
        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }
    
    public function setSize(int $size)
    {
        $this->size = $size;
    
        return $this;
    }

    public function getMeta(): array
    {
        return [
            'total' => $this->total,
            'count' => $this->count,
            'page'  => $this->page,
            'size'  => $this->size,
        ];
    }

    public function setParams($params)
    {
        $this->page  = \intval($params['page']  ?? 1);
        $this->size  = \intval($params['size']  ?? self::DEFAULT_PAGE_SIZE);
        $this->total = \intval($params['total'] ?? 0);

        return $this;
    }
}
