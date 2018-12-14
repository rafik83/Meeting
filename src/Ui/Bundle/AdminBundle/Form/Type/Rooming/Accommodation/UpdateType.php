<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Rooming\Accommodation\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractAccommodationType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Update::class
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'admin_bundle_update_type';
    }
}
