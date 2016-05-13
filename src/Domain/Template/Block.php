<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Template\Object;

class Block extends AbstractChild
{
    /**
     * @var array
     */
    protected $children = [];

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
     * @param int           $column
     * @param string        $name
     * @param AbstractChild $child
     */
    public function addChild($column, $name, AbstractChild $child)
    {
        $this->children[$column][$name] = $child;
    }

    /**
     * @param string $key
     *
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
     * @return \Proximum\Vimeet\Domain\Template\Object[]
     */
    public function getEditableObjects()
    {
        return array_filter($this->getObjects(), function (Object $object) {
            return $object->isEditable();
        });
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
        return $this->getBlock(1);
    }

    /**
     * @param int $index
     *
     * @return null|Block
     */
    public function getBlock($index)
    {
        $count = 0;

        if ($index <= 0) {
            return null;
        }

        foreach ($this->children as $children) {
            foreach ($children as $block) {
                if ($block instanceof Block) {
                    $count++;

                    if (intval($index) === $count) {
                        return $block;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return null|Block
     */
    public function getBlocksCount()
    {
        $blocksCount =  0;

        foreach ($this->children as $children) {
            foreach ($children as $block) {
                if ($block instanceof Block && count($block->getObjects()) > 0) {
                    $blocksCount++;
                }
            }
        }

        return $blocksCount;
    }

    /**
     * @param int $currentBlock
     *
     * @return int|null
     */
    public function getNextBlockPosition($currentBlock)
    {
        $count = 0;

        if ($currentBlock <= 0) {
            return null;
        }

        foreach ($this->children as $children) {
            foreach ($children as $block) {
                if ($block instanceof Block) {
                    $count++;

                    if (intval($currentBlock) < $count && count($block->getObjects()) > 0) {
                        return $count;
                    }
                }
            }
        }

        return null;
    }


    /**
     * @param string $tag
     * @param string $locale
     *
     * @return array;
     */
    public function getTaggedDatas($tag, $locale)
    {
        $tagged = [];

        foreach ($this->children as $children) {
            foreach ($children as $block) {
                if ($block instanceof Block) {
                    $tagged = array_merge($tagged, $block->getTaggedDatas($tag, $locale));
                }

                if ($block instanceof Object) {
                    if ($block->hasTag($tag) && $block instanceof Object\ContentObjectInterface) {
                        if ($block instanceof Object\Nomenclature) {
                            $tagged[] = $block->getNomenclatureLabel();
                        } else {
                            $tagged[] = $block->getContentValue();
                        }
                    }
                }
            }
        }

        return $tagged;
    }

    /**
     * @return Object[]
     */
    public function getImageObjects()
    {
        $objects = [];

        foreach ($this->getObjects() as $object) {
            if ($object instanceof Object\Image) {
                $objects[] = $object;
            }
        }

        return $objects;
    }

    /**
     * @param $locale
     *
     * @return null|string
     */
    public function getTitle($locale)
    {
        foreach ($this->getObjects() as $object) {
            if ($object instanceof Object\Text && $object->isTitle()) {
                return $object->getContent($locale);
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
