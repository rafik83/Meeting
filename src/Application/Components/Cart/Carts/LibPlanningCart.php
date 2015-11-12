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

        if ([] !== $template
            && isset($dataValue['planning'])
            && $dataValue['planning'] === true
        ) {
            $options['label']     = isset($template['label'][$locale]) ? $template['label'][$locale] : null;
            $options['quantity']  = isset($dataValue['planning_bought']) && $dataValue['planning_bought'] !== null ? $dataValue['planning_bought'] : 0;
            $options['unitPrice'] = isset($template['unitPrice']) ? $template['unitPrice'] : null;
            $options['total']     = $options['quantity'] * $options['unitPrice'];
        }

        return $options;
    }
}
