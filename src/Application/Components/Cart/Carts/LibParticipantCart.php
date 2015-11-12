<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart\Carts;

class LibParticipantCart implements LibCartInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepare(array $template, array $dataValue, $locale)
    {
        $options = [];

        if ([] !== $template
            && isset($dataValue)
            && isset($dataValue['participant'])
            && $dataValue['participant'] === true
        ) {
            $options['label']     = isset($template['label'][$locale]) ? $template['label'][$locale] : null;
            $options['quantity']  = isset($dataValue['participant_bought']) && $dataValue['participant_bought'] !== null ? $dataValue['participant_bought'] : 0;
            $options['unitPrice'] = isset($template['unitPrice']) ? $template['unitPrice'] : null;
            $options['total']     = $options['quantity'] * $options['unitPrice'];
        }

        return $options;
    }
}
