<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin;

use Proximum\Vimeet\Application\Command\Admin\ChangePassword;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\ChangePasswordType as AbstractChangePasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractChangePasswordType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ChangePassword::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'change_password_admin';
    }
}
