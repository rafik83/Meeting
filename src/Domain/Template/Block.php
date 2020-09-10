<?php

namespace Proximum\Vimeet\Domain\Template;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainForbiddenObjectsException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainNomenclatureObjectWithDepthHigherThanOneException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectsCollectionBlockCanNotContainOtherBlockException;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadableObjectInterface;

class Block extends AbstractChild
{
    /**
     * @var array
     */
    protected $children = [];

    /**
     * {@inheritdoc}
     */
    public function getComponent()
    {
        return 'block';
    }

    public function isObjectsCollection(): bool
    {
        return $this->getType() === 'objects_collection';
    }

    /**
     * @param string $key
     *
     * @throws \Exception
     *
     * @return TemplateObject
     */
    public function __get($key)
    {
        return $this->getObject($key);
    }

    /**
     * Get children
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
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

    public function getLabel($locale)
    {
        return $this->getOption('label', $locale);
    }

    public function setLabel($label, $locale)
    {
        $this->setOption('label', $label, $locale);
    }

    /**
     * @param int $column
     */
    public function addColumn($column)
    {
        $this->children[$column] = [];
    }

    /**
     * @param int $column
     *
     * @return array
     */
    public function getColumn($column)
    {
        return isset($this->children[$column]) ? $this->children[$column] : [];
    }

    /**
     * @param int           $column
     * @param string        $name
     * @param AbstractChild $child
     *
     * @return Block
     *
     * @throws ObjectsCollectionBlockCanNotContainForbiddenObjectsException
     * @throws ObjectsCollectionBlockCanNotContainOtherBlockException
     */
    public function addChild($column, $name, AbstractChild $child)
    {
        if ($this->isObjectsCollection()) {
            $this->handleObjectsCollection($child);
        }

        $this->children[$column][$name] = $child;

        return $this;
    }

    /**
     * This function returns all the tags present on the block's children
     *
     * @return array
     */
    public function getTags(): array
    {
        $tags = [];

        foreach ($this->getObjects() as $child) {
            $tag = $child->getTag();

            if (null !== $tag) {
                $tags[$tag] = $tag;
            }

            foreach ($child->getTags() as $tagInfo) {
                if (isset($tagInfo['tag'])) {
                    $tags[$tagInfo['tag']] = $tagInfo['tag'];
                }
            }
        }

        return $tags;
    }

    /**
     * Get all blocks on 1 level
     *
     * @return Block[]
     */
    public function getBlocks()
    {
        return array_reduce($this->children, static function ($carry, $columns) {
            return array_merge($carry, array_values(array_filter($columns, static function (AbstractChild $child) {
                return $child instanceof Block;
            })));
        }, []);
    }

    public function getBlocksIndexedByUid()
    {
        return array_reduce($this->children, static function (array $carry, array $column) {
            foreach ($column as $childKey => $child) {

                if ($child instanceof Block) {
                    $carry = array_merge($carry, [$childKey => $child], $child->getBlocksIndexedByUid());
                }
            }

            return $carry;
        }, []);
    }

    public function getBlockByUid(string $uid): ?Block
    {
        $blocks = $this->getBlocksIndexedByUid();

        if (isset($blocks[$uid])) {
            return $blocks[$uid];
        }

        return null;
    }

    /**
     * @param string $tag
     *
     * @return null|TemplateObject
     */
    public function getObjectByTag(string $tag): ?TemplateObject
    {
        foreach ($this->getObjects() as $object) {
            if ($object->hasTag($tag)) {
                return $object;
            }
        }

        return null;
    }

    /**
     * @param string|null $key
     *
     * @return TemplateObject[]
     */
    public function getObjects($key = null)
    {
        return array_reduce($this->children, static function (array $carry, array $column) use ($key) {
            foreach ($column as $childKey => $child) {
                if (null !== $key && $childKey !== $key) {
                    continue;
                }

                if ($child instanceof Block) {
                    $carry = array_merge($carry, $child->getObjects());
                } elseif ($child instanceof TemplateObject) {
                    $carry = array_merge($carry, [$childKey => $child]);
                }
            }

            return $carry;
        }, []);
    }

    /**
     * @param string $key
     */
    public function removeObject(string $key)
    {
        foreach ($this->children as $columnKey => $column) {
            foreach ($column as $childKey => $child) {
                if ($child instanceof self) {
                    $child->removeObject($key);
                } elseif ($childKey === $key) {
                    unset($this->children[$columnKey][$childKey]);

                    return;
                }
            }
        }
    }

    /**
     * @return TemplateObject\ContentObjectInterface[]
     */
    public function getContentObjects()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object instanceof TemplateObject\ContentObjectInterface;
        });
    }

    /**
     * @return TemplateObject\ExportableObjectInterface[]
     */
    public function getExportableObjects()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object instanceof TemplateObject\ExportableObjectInterface;
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getEditableObjects(): array
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object->isEditable() && $object->isVisibilityEditable();
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getHiddenAndReadOnlyObjects(): array
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object->isVisibilityHidden() || $object->isVisibilityReadOnly();
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getReadOnlyObjects(): array
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object->isVisibilityReadOnly();
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getObjectsEditableByAdmin(): array
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return ($object->isEditable() && $object->isVisibilityEditable())
                || $object->isVisibilityHidden()
                || $object->isVisibilityReadOnly();
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getProfileObjects()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object->isEditable() && $object->hasTag(Tag::PARTICIPANT_DATA) && !$object instanceof TemplateObject\Image;
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getEditableSheetDataExceptedImageObjects()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object->isEditable() && $object->hasTag(Tag::SHEET_DATA) && !$object instanceof TemplateObject\Image;
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getSheetObjects()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object->hasTag(Tag::SHEET_DATA);
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getParticipantAndSheetDataExceptedImageObject()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            if ($object instanceof TemplateObject\Image) {
                return false;
            }

            return $object->isEditable() && ($object->hasTag(Tag::SHEET_DATA) || $object->hasTag(Tag::PARTICIPANT_DATA));
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getEditableTextAndNomenclatureObjects(): array
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object->isEditable()
                && ($object instanceof TemplateObject\EditableText || $object instanceof TemplateObject\Nomenclature)
            ;
        });
    }

    /**
     * @deprecated Use {@link Block::getEditableSheetDataExceptedImageObjects()} instead
     *
     * @return TemplateObject[]
     */
    public function getCompanyObjects()
    {
        return $this->getEditableSheetDataExceptedImageObjects();
    }

    /**
     * @return TemplateObject[]
     */
    public function getAvatarObjects()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object->isEditable() && $object->hasTag(Tag::PARTICIPANT_DATA) && $object->hasTag(Tag::PARTICIPANT_AVATAR) && $object instanceof TemplateObject\Image;
        });
    }

    /**
     * @return TemplateObject[]
     */
    public function getPreviewAvailableObjects(): array
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object instanceof TemplateObject\Image
                || $object instanceof TemplateObject\EditableText
                || $object instanceof TemplateObject\Participant
                || $object instanceof TemplateObject\Tag
                || $object instanceof TemplateObject\Video
            ;
        });
    }

    /**
     * @param string $key
     *
     * @throws ObjectNotFoundException
     *
     * @return TemplateObject
     */
    public function getObject($key)
    {
        $objects = $this->getObjects();

        if (isset($objects[$key])) {
            return $objects[$key];
        }

        throw new ObjectNotFoundException($key);
    }

    /**
     * @param string $key
     *
     * @throws \Exception
     *
     * @return bool
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
     * Get a first level block by its index (starts from 1)
     *
     * @param int $index
     *
     * @return null|Block
     */
    public function getBlock($index)
    {
        $blocks = $this->getBlocks();
        $index  = (int) $index - 1;

        return $blocks[$index] ?? null;
    }

    /**
     * Count first level blocks having objects
     *
     * @return int
     */
    public function getBlocksCount(): int
    {
        return \count(array_filter($this->getBlocks(), static function (Block $block) {
            return \count($block->getObjects()) > 0;
        }));
    }

    /**
     * @param int $current
     *
     * @return int|null
     */
    public function getNextBlockPosition($current)
    {
        return $current < $this->getBlocksCount() ? ++$current : null;
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
                if ($block instanceof self) {
                    $tagged = array_merge($tagged, $block->getTaggedDatas($tag));
                }

                if ($block instanceof TemplateObject) {
                    if ($block->hasTag($tag) && $block instanceof TemplateObject\ContentObjectInterface) {
                        if ($block instanceof TemplateObject\Nomenclature) {
                            $tagged[] = implode(', ', $block->getNomenclatureLabelOfItems());
                        } elseif ($block instanceof TemplateObject\DateTime) {
                            $tagged[] = $block->getFormattedDate($block->getLocale());
                        } else {
                            $tagged[] = $block->getContentValueLocalize();
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
                if (!$object instanceof TemplateObject\ContentObjectInterface) {
                    continue;
                }

                if ($object instanceof TemplateObject\Nomenclature) {
                    $tagged[$tag][] = implode(', ', $object->getNomenclatureLabelOfItems());
                } elseif ($object instanceof TemplateObject\DateTime) {
                    $tagged[$tag][] = $object->getFormattedDate($object->getLocale());
                } else {
                    $tagged[$tag][] = $object->getContentValue();
                }
            }
        }

        return $tagged;
    }

    /**
     * @param string $tag
     *
     * @return mixed
     */
    public function getTaggedContentLabel($tag)
    {
        $objects = new ArrayCollection($this->getObjects());

        return $objects->filter(function (TemplateObject $object) use ($tag) {
            return $object instanceof TemplateObject\ContentObjectInterface && $object->hasTag($tag);
        })->map(function (TemplateObject\ContentObjectInterface $object) {
            return $object->getContentLabel();
        })->first();
    }

    /**
     * @param string $tag
     *
     * @return mixed
     */
    public function getTaggedContentValue($tag)
    {
        $objects = new ArrayCollection($this->getObjects());

        return $objects->filter(function (TemplateObject $object) use ($tag) {
            return $object instanceof TemplateObject\ContentObjectInterface && $object->hasTag($tag);
        })->map(function (TemplateObject\ContentObjectInterface $object) {
            return $object->getContentValueLocalize();
        })->first();
    }

    /**
     * @return TemplateObject\UploadableObjectInterface[]
     */
    public function getUploadAndImageObjects(): array
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object instanceof UploadableObjectInterface;
        });
    }

    /**
     * @return TemplateObject\MediaCollection[]
     */
    public function getMediaCollectionObjects()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object instanceof TemplateObject\MediaCollection;
        });
    }

    /**
     * @return TemplateObject\Nomenclature[]
     */
    public function getNomenclatureObjects()
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) {
            return $object instanceof TemplateObject\Nomenclature;
        });
    }

    /**
     * @param string $objective
     *
     * @return TemplateObject\Nomenclature[]
     */
    public function getNomenclatureObjectsByObjective($objective)
    {
        return array_filter($this->getObjects(), static function (TemplateObject $object) use ($objective) {
            return $object instanceof TemplateObject\Nomenclature && $object->getObjective() === $objective;
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
     * @param $locale
     *
     * @return null|string
     */
    public function getDescription($locale)
    {
        foreach ($this->getObjects() as $object) {
            if ($object instanceof TemplateObject\Text && !$object->isTitle()) {
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
            'children'  => array_map(static function (array $column) {
                return array_map(static function (AbstractChild $child) {
                    return $child->normalize();
                }, $column);
            }, $this->children),
        ];

        return 'root' === $this->type ? $array['children'][0] : $array;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array_map(static function (TemplateObject $object) {
            return $object->getData();
        }, $this->getObjects());
    }

    /**
     * @return array
     */
    public function getSheetData()
    {
        return array_map(static function (TemplateObject $object) {
            return $object->getData();
        }, $this->getSheetObjects());
    }

    /**
     * Clear objects data
     */
    public function clear(): void
    {
        array_map(static function (TemplateObject $object) {
            $object->setData([]);
        }, $this->getObjects());
    }

    /**
     * @param array $data
     *
     * @return Block
     */
    public function setTaggedData(array $data)
    {
        foreach ($this->getObjects() as $object) {
            foreach ($data as $tag => $value) {
                if ($object->hasTag($tag) && $object instanceof TemplateObject\ContentObjectInterface) {
                    $object->setContentValue($value);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $data
     *
     * @return Block
     */
    public function setTaggedDataIfEmpty(array $data): Block
    {
        foreach ($this->getObjects() as $object) {
            foreach ($data as $tag => $value) {
                if ($object->hasTag($tag)
                    && $object instanceof TemplateObject\ContentObjectInterface
                    && empty($object->getContentValue())
                ) {
                    $object->setContentValue($value);
                }
            }
        }

        return $this;
    }

    /**
     * @param array $taggedDataViews
     *
     * @return Block
     */
    public function setTaggedDataViews(array $taggedDataViews): Block
    {
        /** @var TemplateObject $object */
        foreach ($this->getObjects() as $object) {
            $tags = $object instanceof TemplateObject\EditableText && !empty($object->getTag())
                ? [$object->getTag()]
                : $object->getTags()
            ;

            if (0 === \count($tags)) {
                continue;
            }

            foreach ($tags as $tagData) {
                $tag = isset($tagData['tag']) ? $tagData['tag'] : $tagData;

                if (\in_array($tag, Tag::getSetters(), true)) {
                    continue;
                }

                if (!empty($taggedDataViews[$tag])) {
                    $object->addTaggedDataView($taggedDataViews[$tag]);
                }
            }
        }

        return $this;
    }

    /**
     * @param string            $fieldName
     * @param array|string|null $emptyValue
     */
    public function removeField($fieldName, $emptyValue)
    {
        foreach ($this->getObjects() as $object) {
            if (null !== $object->getOption($fieldName)) {
                $object->setOption($fieldName, $emptyValue);
            }
        }
    }

    /**
     * @return TemplateObject[]
     */
    public function getUserIdentityObjects()
    {
        $objects = [];

        foreach ($this->getObjects() as $object) {
            foreach (Tag::getParticipantIdentityTags() as $tag) {
                if ($object->hasTag($tag)) {
                    $objects[$tag] = $object;
                }
            }
        }

        return $objects;
    }

    /**
     * @return string[]
     */
    public function getObjectsLabel(): array
    {
        $objectsLabel = [];

        foreach ($this->getObjects() as $templateObject) {
            $objectsLabel[] = $templateObject->getLabel($this->getLocale(), $this->getFallback());
        }

        return $objectsLabel;
    }

    public function getObjectsCollectionContent(): array
    {
        if (!$this->isObjectsCollection()) {
            throw new \LogicException(
                'getObjectsCollectionContent() method can not be used if block is not a objectsCollection'
            );
        }

        $locale = $this->getLocale();
        $objectsContent = [];

        foreach ($this->getObjects() as $uid => $object) {
            $initialData = $object->getData();

            if ($object instanceof EditableText) {
                if ($object->isTranslatable()) {
                    $values = $initialData[EditableText::TEXT][$locale] ?? [];
                } else {
                    $values = $initialData[EditableText::TEXT] ?? [];
                }

                foreach ($values as $index => $value) {
                    $objectsContent[$index][$uid] = $value;
                }

                continue;
            }

            if ($object instanceof Nomenclature) {
                $values = $initialData[Nomenclature::ITEMS] ?? [];

                foreach ($values as $index => $value) {
                    if ($object->isMultiple()) {
                        $objectsContent[$index][$uid] = array_map(
                            static function (string $key) use ($object) {
                                return $object->getLabelForKey($key);
                            },
                            $value
                        );

                        continue;
                    }

                    $objectsContent[$index][$uid] = $object->getLabelForKey($value);
                }

                continue;
            }
        }

        return $objectsContent;
    }

    public function getMaxItems(): int
    {
        return (int) $this->getOption('maxItems');
    }

    /**
     * @throws ObjectsCollectionBlockCanNotContainForbiddenObjectsException
     * @throws ObjectsCollectionBlockCanNotContainOtherBlockException
     */
    private function handleObjectsCollection(AbstractChild $child): void
    {
        if ($child instanceof Block) {
            throw new ObjectsCollectionBlockCanNotContainOtherBlockException();
        }

        $acceptedObjectsInObjectsCollection = $child instanceof EditableText || $child instanceof Nomenclature;

        if (!$acceptedObjectsInObjectsCollection) {
            throw new ObjectsCollectionBlockCanNotContainForbiddenObjectsException();
        }

        if ($child instanceof Nomenclature
            && $child->getNomenclatureModel() instanceof NomenclatureModel
            && $child->getNomenclatureModel()->getDepth() > 1
        ) {
            throw new ObjectsCollectionBlockCanNotContainNomenclatureObjectWithDepthHigherThanOneException();
        }
    }
}
