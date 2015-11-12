<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;


class LibUploadWithChoicesCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $options = [];

        if([] !== $template
            && isset($dataValue['value']['value'])
            && !isset($template['choices'][$dataValue['value']['value']]['placeholder'])
        ) {
            $options['label']     = $template['label'][$locale] . ' : ' . $template['choices'][$dataValue['value']['value']]['label'][$locale];
            $options['quantity']  = isset($dataValue['value']['quantity']) ? $dataValue['value']['quantity'] : 1;
            $options['unitPrice'] = isset($template['choices'][$dataValue['value']['value']]['unitPrice']) ? $template['choices'][$dataValue['value']['value']]['unitPrice'] : null;
            $options['total']     = $options['quantity'] * $options['unitPrice'];
        }

        return $options;
    }
}
