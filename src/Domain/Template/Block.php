<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Template\Object\EditableText;

class Block extends AbstractChild
{
    /**
     * @var array
     */
    private $children = [];

    /**
     * @param string $key
     *
     * @return Object
     * @throws \Exception
     */
    public function __get($key)
    {
        return $this->getObject($key);
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Object
     * @throws \Exception
     */
    public function __set($key, $value)
    {
        $object = $this->getObject($key);

        if ($object instanceof EditableText) {
            return $object->setContent($value);
        }

        return $object->setData($value);
    }

    /**
     * @param int           $column
     * @param string        $name
     * @param AbstractChild $child
     */
    public function addChild($column, $name, AbstractChild $child)
    {
        $this->children[$column][$name] = $child;
    }

    /**
     * @return Object[]
     */
    public function getObjects($key = null)
    {
        return array_reduce($this->children, function (array $carry, array $column) use ($key) {
            foreach ($column as $childKey => $child) {
                if (null !== $key && $childKey !== $key) {
                    continue;
                }

                if ($child instanceof Block) {
                    $carry = array_merge($carry, $child->getObjects());
                } elseif ($child instanceof Object) {
                    $carry = array_merge($carry, [$childKey => $child]);
                }
            }

            return $carry;
        }, []);
    }

    /**
     * @param string $key
     *
     * @return Object
     * @throws \Exception
     */
    public function getObject($key)
    {
        $objects = $this->getObjects();

        if (isset($objects[$key])) {
            return $objects[$key];
        }

        throw new \Exception('Object not found.');
    }

    /**
     * @return null|Block
     */
    public function getFirstBlock()
    {
        foreach ($this->children as $children) {
            foreach ($children as $child) {
                if ($child instanceof Block) {
                    return $child;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize()
    {
        $array = [
            'component' => 'block',
            'type'      => $this->type,
            'config'    => $this->config,
            'children'  => array_map(function (array $column) {
                return array_map(function (AbstractChild $child) {
                    return $child->normalize();
                }, $column);
            }, $this->children),
        ];

        return $this->type === 'root' ? $array['children'][0] : $array;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array_map(function (Object $object) {
            return $object->getData();
        }, $this->getObjects());
    }
}
