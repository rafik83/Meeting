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

        if ([] !== $template
            && isset($dataValue['value'])
            && $dataValue['value'] !== false
        ) {
            $options['label']     = isset($template['label'][$locale]) ? $template['label'][$locale] : null;
            $options['quantity']  = isset($dataValue['quantity']) && $dataValue['quantity'] !== null ? $dataValue['quantity'] : 1;
            $options['unitPrice'] = isset($template['unitPrice']) ? $template['unitPrice'] : null;
            $options['total']     = $options['quantity'] * $options['unitPrice'];
        }

        return $options;
    }
}
