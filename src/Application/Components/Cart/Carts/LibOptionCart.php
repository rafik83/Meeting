<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;


class LibOptionCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $options = [];

        if (isset($dataValue['value']) && $dataValue['value'] !== false) {
            $options['label']     = $template['label'][$locale];
            $options['unitPrice'] = $template['unitPrice'];
            $options['quantity']  = isset($dataValue['quantity']) && $dataValue['quantity'] !== null ? $dataValue['quantity'] : 1;
            $options['total']     = $options['quantity'] * $options['unitPrice'];
        }

        return $options;
    }
}
