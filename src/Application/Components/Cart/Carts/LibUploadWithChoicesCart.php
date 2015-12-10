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

class LibUploadWithChoicesCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $cartRow = null;

        if ([] !== $template
            && isset($dataValue['value']['value'])
            && !isset($template['choices'][$dataValue['value']['value']]['placeholder'])
        ) {
            $cartRow = new CartRow(
                $template['label'][$locale].' : '.$template['choices'][$dataValue['value']['value']]['label'][$locale],
                isset($dataValue['value']['quantity']) ? $dataValue['value']['quantity'] : 1,
                isset($template['choices'][$dataValue['value']['value']]['unitPrice']) ? $template['choices'][$dataValue['value']['value']]['unitPrice'] : null
            );
        }

        return $cartRow;
    }
}
