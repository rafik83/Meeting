<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ItemsToCheckboxTransformer extends AbstractTransformer
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
            throw new TransformationFailedException(sprintf('"array" expected, "%s" given', \gettype($value)));
        }

        $items = $this->nomenclature->getLastLevel();

        return array_map(function ($key) use ($items) {
            return self::findByKey($items, $key);
        }, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return $value;
        }

        if (!\is_array($value)) {
            throw new TransformationFailedException(sprintf('"array" expected, "%s" given', \gettype($value)));
        }

        return array_map(function (NomenclatureItem $item) {
            return $item->getKey();
        }, $value);
    }
}
