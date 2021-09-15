<?php

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

        if (empty($result)) {
            return $this->createEmptyCollectionItem($block);
        }

        return $result;
    }

    private function handleEditableText(array &$result, string $uid, EditableText $object, array &$initialData): void
    {
        $initialData = $object->getData();
        $values = $initialData[EditableText::TEXT] ?? [];

        if ($object->isTranslatable()) {
            foreach ($values as $locale => $translationItems) {
                if (!\is_array($translationItems)) {
                    continue;
                }

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

    /**
     * @param Block $block
     *
     * @return array example: [['uid123' => null, 'uid963' => null]]
     */
    private function createEmptyCollectionItem(Block $block): array
    {
        $objectsCollectionDataItem = [];

        foreach (array_keys($block->getObjects()) as $uid) {
            $objectsCollectionDataItem[$uid] = null;
        }

        return [$objectsCollectionDataItem];
    }
}
