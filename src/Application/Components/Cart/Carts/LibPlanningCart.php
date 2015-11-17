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

class LibPlanningCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $cartRow = null;

        if ([] !== $template
            && isset($dataValue['planning'])
            && $dataValue['planning'] === true
            && isset($dataValue['planning_bought'])
            && $dataValue['planning_bought'] !== 0
        ) {
            $cartRow = new CartRow(
                isset($template['label'][$locale]) ? $template['label'][$locale] : null,
                isset($dataValue['planning_bought']) && $dataValue['planning_bought'] !== null ? $dataValue['planning_bought'] : 0,
                isset($template['unitPrice']) ? $template['unitPrice'] : null
            );
        }

        return $cartRow;
    }
}
