<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;

class LibChoiceWithDescriptionCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $options = [];

        if ([] !== $template
            && isset($dataValue)
            && isset($dataValue['value'])
        ) {
            $options['label']     = $template['label'][$locale] . ' : ' . $template['choices'][$dataValue['value']]['label'][$locale];
            $options['quantity']  = isset($template['choices'][$dataValue['value']]['quantity']) ? $template['choices'][$dataValue['value']]['quantity'] : 1;
            $options['unitPrice'] = isset($template['choices'][$dataValue['value']]['unitPrice']) ? $template['choices'][$dataValue['value']]['unitPrice'] : null;
            $options['total']     = $options['quantity'] * $options['unitPrice'];
        }

        return $options;
    }
}
