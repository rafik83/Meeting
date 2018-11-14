<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\ObjectsCollectionBlock;

use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class BlockToArray
{
    public function __invoke(Block $block): array
    {
        if (!$block->isObjectsCollection()) {
            throw new \InvalidArgumentException('Given block is not an objects collection');
        }

        $result = [];

        foreach ($block->getObjects() as $uid => $object) {
            $initialData = $object->getData();

            if ($object instanceof EditableText) {
                $this->handleEditableText($result, $uid, $object, $initialData);
            }

            if ($object instanceof Nomenclature) {
                $this->handleNomenclature($result, $uid, $object, $initialData);
            }
        }

        return $result;
    }

    private function handleEditableText(array &$result, string $uid, EditableText $object, array &$initialData): void
    {
        $initialData = $object->getData();
        $values = $initialData[EditableText::TEXT] ?? [];

        if ($object->isTranslatable()) {
            foreach ($values as $locale => $translationItems) {
                foreach ($translationItems as $index => $value) {
                    $result[$index][$uid]['translationsInput'][$locale] = [EditableText::CONTENT => $value];
                }
            }

            return;
        }

        foreach ($values as $index => $value) {
            $result[$index][$uid] = [EditableText::CONTENT => $value];
        }
    }

    private function handleNomenclature(array &$result, string $uid, Nomenclature $object, array &$initialData): void
    {
        $values = $initialData[Nomenclature::ITEMS] ?? [];

        foreach ($values as $index => $value) {
            $result[$index][$uid] = $object->isMultiple()
                ? [Nomenclature::ITEMS => $value]
                : [Nomenclature::ITEM => $value];
        }
    }
}
