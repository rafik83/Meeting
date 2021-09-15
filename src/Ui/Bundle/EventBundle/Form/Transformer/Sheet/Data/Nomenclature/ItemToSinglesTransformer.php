<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ItemToSinglesTransformer extends AbstractTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (empty($value)) {
            return $value;
        }

        if (\is_array($value)) {
            $value = reset($value);

            if (false === $value) {
                $value = '';
            }
        }

        if (!\is_string($value)) {
            // A string is expected, therefore it is cast to string
            $value = (string) $value;
        }

        $item  = self::findByKey($this->nomenclature->getLastLevel(), $value);
        $depth = $this->nomenclature->getDepth();

        if (null === $item) {
            // Case of participation type change => when old nomenclature data aren't matching with the news ones
            return [];
        }

        if (1 === $depth) {
            return [
                'first' => $item,
            ];
        } elseif (2 === $depth) {
            return [
                'first'  => $item->getParent(),
                'second' => $item,
            ];
        } elseif (3 === $depth) {
            return [
                'first'  => $item->getParent()->getParent(),
                'second' => $item->getParent(),
                'third'  => $item,
            ];
        }

        throw new TransformationFailedException(sprintf('Unable to handle depth of %s', $depth));
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        $positions = [1 => 'first', 2 => 'second', 3 => 'third'];
        $depth     = $this->nomenclature->getDepth();
        $item      = isset($positions[$depth]) && isset($value[$positions[$depth]]) ? $value[$positions[$depth]] : null;

        if (empty($item)) {
            return $item;
        }

        if (!$item instanceof NomenclatureItem) {
            throw new TransformationFailedException(sprintf('"%s" expected, "%s" given', NomenclatureItem::class, \gettype($value)));
        }

        return $item->getKey();
    }
}
