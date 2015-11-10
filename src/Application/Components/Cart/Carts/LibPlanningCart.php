<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;

class LibPlanningCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $options = [];

        if (isset($dataValue['planning']) && $dataValue['planning'] === true) {
            $options['label']     = $template['label'][$locale];
            $options['quantity']  = isset($dataValue['planning_bought']) && $dataValue['planning_bought'] !== null ? $dataValue['planning_bought'] : 0;
            $options['unitPrice'] = $template['unitPrice'];
            $options['total']     = $options['quantity'] * $options['unitPrice'];
        }

        return $options;
    }
}
