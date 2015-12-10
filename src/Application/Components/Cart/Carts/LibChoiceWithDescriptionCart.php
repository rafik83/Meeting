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

class LibChoiceWithDescriptionCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $cartRow = null;

        if ([] !== $template
            && isset($dataValue)
            && isset($dataValue['value'])
        ) {
            $cartRow = new CartRow(
                $template['label'][$locale].' : '.$template['choices'][$dataValue['value']]['label'][$locale],
                isset($template['choices'][$dataValue['value']]['quantity']) ? $template['choices'][$dataValue['value']]['quantity'] : 1,
                isset($template['choices'][$dataValue['value']]['unitPrice']) ? $template['choices'][$dataValue['value']]['unitPrice'] : null
            );
        }

        return $cartRow;
    }
}
