<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Planning;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant;

class TranslationsType extends Participant\TranslationsType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'participant_translation';
    }
}
