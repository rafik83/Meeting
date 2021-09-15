<?php

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

            foreach ($data as $collectionItem) {
                if (!isset($collectionItem[$uid])) {
                    continue;
                }

                $collectionItemObjectField = $collectionItem[$uid];

                if ($object instanceof EditableText) {
                    if ($object->isTranslatable()) {
                        foreach ($collectionItemObjectField['translationsInput'] as $locale => $translations) {
                            $content[$locale][] = $translations[EditableText::CONTENT];
                        }
                    } else {
                        $content[] = $collectionItemObjectField[EditableText::CONTENT] ?? null;
                    }

                    continue;
                }

                if ($object instanceof Nomenclature) {
                    if ($object->isMultiple()) {
                        $content[] = $collectionItemObjectField[Nomenclature::ITEMS] ?? null;
                    } else {
                        $content[] = $collectionItemObjectField[Nomenclature::ITEM] ?? null;
                    }

                    continue;
                }
            }

            if ($object instanceof EditableText && $object->isTranslatable()) {
                $object->setTranslations($content);

                continue;
            }

            if ($object instanceof ContentObjectInterface) {
                $object->setContentValue($content);
            }
        }
    }
}
