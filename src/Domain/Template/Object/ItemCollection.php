<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

use Proximum\Vimeet\Domain\Template\Object;

class ItemCollection extends Object
{
    /**
     * @var Item[]
     */
    private $items = [];

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $data = array_merge(['items' => []], $data);

        $this->items = array_map(function (array $item) {
            return new Item($this, $item['title']);
        }, array_values($data['items']));

        return parent::setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->data['items'] = array_values(array_map(function (Item $item) {
            return $item->getData();
        }, $this->items));

        return parent::getData();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item $item
     *
     * @return ItemCollection
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param Item $item
     *
     * @return ItemCollection
     */
    public function removeItem(Item $item)
    {
        foreach ($this->items as $key => $value) {
            if ($item === $value) {
                unset($this->items[$key]);
            }
        }

        return $this;
    }
}