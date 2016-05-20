<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

        if (!is_string($value)) {
            throw new TransformationFailedException(sprintf('"string" expected, "%s" given', gettype($value)));
        }

        $item  = self::findByKey($this->nomenclature->getLastLevel(), $value);
        $depth = $this->nomenclature->getDepth();

        if ($depth === 1) {
            return [
                'first' => $item,
            ];
        } elseif ($depth === 2) {
            return [
                'first'  => $item->getParent(),
                'second' => $item,
            ];
        } elseif ($depth === 3) {
            return [
                'first'  => $item->getParent()->getParent(),
                'second' => $item->getParent(),
                'third'  => $item,
            ];
        } else {
            throw new TransformationFailedException(sprintf('Unable to handle depth of %s', $depth));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return $value;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('"array" expected, "%s" given', gettype($value)));
        }

        $positions = [1 => 'first', 2 => 'second', 3 => 'third'];
        $depth     = $this->nomenclature->getDepth();
        $item      = isset($positions[$depth]) && isset($value[$positions[$depth]]) ? $value[$positions[$depth]] : null;

        if (!$item instanceof NomenclatureItem) {
            throw new TransformationFailedException();
        }

        return $item->getKey();
    }
}
