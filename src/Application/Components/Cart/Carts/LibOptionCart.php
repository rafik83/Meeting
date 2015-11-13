<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;

use Proximum\Vimeet\Application\Components\Cart\CartRow;

class LibOptionCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $cartRow = null;

        if ([] !== $template
            && isset($dataValue['value'])
            && $dataValue['value'] !== false
        ) {
            $cartRow = new CartRow(
                isset($template['label'][$locale]) ? $template['label'][$locale] : null,
                isset($dataValue['quantity']) && $dataValue['quantity'] !== null ? $dataValue['quantity'] : 1,
                isset($template['unitPrice']) ? $template['unitPrice'] : null
            );
        }

        return $cartRow;
    }
}
