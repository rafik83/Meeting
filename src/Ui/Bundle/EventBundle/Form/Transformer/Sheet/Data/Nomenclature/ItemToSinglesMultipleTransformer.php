<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * DataTransformer for usage of SingleType with multiple choice
 */
class ItemToSinglesMultipleTransformer extends AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (empty($value)) {
            return $value;
        }

        if (!\is_array($value)) {
            $value = (array) $value;
        }

        $items = [];
        foreach ($value as $item) {
            $items[] = self::findByKey($this->nomenclature->getLastLevel(), $item);
        }

        if (empty($items)) {
            return [];
        }

        return [
            'first' => $items,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        // Only the first depth is handle
        $depth = $this->nomenclature->getDepth();

        if (1 !== $depth) {
            return [];
        }

        $items = $value['first'] ?? [];

        if (empty($items)) {
            return $items;
        }

        $values = [];

        foreach ($items as $item) {
            if (!$item instanceof NomenclatureItem) {
                throw new TransformationFailedException(
                    sprintf('"%s" expected, "%s" given', NomenclatureItem::class, \gettype($value))
                );
            }

            $values[] = $item->getKey();
        }

        return $values;
    }
}
