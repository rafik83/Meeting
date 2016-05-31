<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Template\Object as TemplateObject;

class Block extends AbstractChild
{
    /**
     * @var array
     */
    protected $children = [];

    /**
     * @param string $key
     *
     * @return TemplateObject
     * @throws \Exception
     */
    public function __get($key)
    {
        return $this->getObject($key);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getOption('enabled');
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->setOption('enabled', $enabled);
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }

    /**
     * @param string $label
     * @param string $locale
     */
    public function setLabel($label)
    {
        $this->setOption('label', $label);
    }

    /**
     * @param int $column
     */
    public function addColumn($column)
    {
        $this->children[$column] = [];
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
     * @return Block[]
     */
    public function getBlocks()
    {
        $blocks = [];

        foreach ($this->children as $children) {
            foreach ($children as $block) {
                if ($block instanceof Block) {
                    $blocks[] = $block;
                }
            }
        }

        return $blocks;
    }

    /**
     * @return TemplateObject[]
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
     * @return TemplateObject[]
     */
    public function getEditableObjects()
    {
        return array_filter($this->getObjects(), function (Object $object) {
            return $object->isEditable();
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getProfileObjects()
    {
        return array_filter($this->getObjects(), function (Object $object) {
            return $object->isEditable() && $object->hasTag(Tag::PARTICIPANT_DATA) && !$object instanceof Object\Image;
        });
    }

    /**
     * @param string $key
     *
     * @return TemplateObject
     * @throws \Exception
     */
    public function getObject($key)
    {
        $objects = $this->getObjects();

        if (isset($objects[$key])) {
            return $objects[$key];
        }

        throw new \Exception("Object $key not found.");
    }

    /**
     * @param string $key
     *
     * @return bool
     * @throws \Exception
     */
    public function hasObject($key)
    {
        $objects = $this->getObjects();

        return isset($objects[$key]);
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
     *
     * @return array
     */
    public function getTaggedDatas($tag)
    {
        $tagged = [];

        foreach ($this->children as $children) {
            foreach ($children as $block) {
                if ($block instanceof Block) {
                    $tagged = array_merge($tagged, $block->getTaggedDatas($tag));
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
     * @return array
     */
    public function getAllTaggedDatas()
    {
        $tagged = [];

        foreach ($this->getEditableObjects() as $object) {
            foreach ($object->getTags() as $tag) {
                if (!$object instanceof Object\ContentObjectInterface) {
                    continue;
                }

                if ($object instanceof Object\Nomenclature) {
                    $tagged[$tag][] = $object->getNomenclatureLabel();
                } else {
                    $tagged[$tag][] = $object->getContentValue();
                }
            }
        }

        return $tagged;
    }

    /**
     * @return TemplateObject\Image[]
     */
    public function getImageObjects()
    {
        return array_filter($this->getObjects(), function (TemplateObject $object) {
            return $object instanceof TemplateObject\Image;
        });
    }

    /**
     * @param $locale
     *
     * @return null|string
     */
    public function getTitle($locale)
    {
        foreach ($this->getObjects() as $object) {
            if ($object instanceof TemplateObject\Text && $object->isTitle()) {
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
        return array_map(function (TemplateObject $object) {
            return $object->getData();
        }, $this->getObjects());
    }
}
