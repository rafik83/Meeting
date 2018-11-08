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

        $locale = $block->getLocale();
        $result = [];

        foreach ($block->getObjects() as $uid => $object) {
            $initialData = $object->getData();

            if ($object instanceof EditableText) {
                if ($object->isTranslatable()) {
                    $values = $initialData[EditableText::TEXT][$locale] ?? [];
                } else {
                    $values = $initialData[EditableText::TEXT] ?? [];
                }

                foreach ($values as $index => $value) {
                    $result[$index][$uid] = [EditableText::CONTENT => $value];
                }

                continue;
            }

            if ($object instanceof Nomenclature) {
                $values = $initialData[Nomenclature::ITEMS] ?? [];

                foreach ($values as $index => $value) {
                    $result[$index][$uid] = [Nomenclature::ITEMS => $value];
                }

                continue;
            }
        }

        return $result;
    }
}
