<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\User;

use Proximum\Vimeet\Application\Command\User\ActivateAccountPassword;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivateAccountPasswordType extends AbstractPasswordType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ActivateAccountPassword::class,
        ]);
    }
}
