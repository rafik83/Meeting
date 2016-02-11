<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening\Speaker;

use Proximum\Vimeet\Application\Command\Happening\Speaker\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateSpeakerType extends AbstractSpeakerType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Update::class,
            'submit'     => true,
        ]);
    }
}
