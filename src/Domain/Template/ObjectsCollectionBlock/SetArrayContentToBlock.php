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
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class SetArrayContentToBlock
{
    public function __invoke(Block $block, array $data): void
    {
        foreach ($block->getObjects() as $uid => $object) {
            $content = [];

            foreach ($data as $collectionRow) {
                if ($object instanceof EditableText) {
                    $content[] = $collectionRow[$uid][EditableText::CONTENT] ?? null;

                    continue;
                }

                if ($object instanceof Nomenclature) {
                    if ($object->isMultiple()) {
                        $content[] = $collectionRow[$uid][Nomenclature::ITEMS] ?? null;

                        continue;
                    }

                    $content[] = $collectionRow[$uid][Nomenclature::ITEM] ?? null;

                    continue;
                }

                $content[] = $collectionRow[$uid] ?? null;
            }

            if ($object instanceof ContentObjectInterface) {
                $object->setContentValue($content);
            }
        }
    }
}
