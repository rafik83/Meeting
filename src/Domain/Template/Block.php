<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

class Block extends AbstractChild
{
    /**
     * @var array
     */
    private $children = [];

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
    public function getObjects()
    {
        return array_reduce($this->children, function (array $carry, array $column) {
            foreach ($column as $key => $child) {
                if ($child instanceof Block) {
                    $carry = array_merge($carry, $child->getObjects());
                } elseif ($child instanceof Object) {
                    $carry = array_merge($carry, [$key => $child]);
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
}
