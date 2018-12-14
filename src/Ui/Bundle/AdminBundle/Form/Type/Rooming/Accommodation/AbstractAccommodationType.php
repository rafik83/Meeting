<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Rooming\Accommodation\AccommodationOvernightCapacityView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractAccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [])
            ->add('overnightCapacities', CollectionType::class, [
                'entry_type' => AccommodationOvernightCapacityType::class,
                'allow_add' => true,
                'allow_delete' => false,
                'prototype' => true,
                //@todo Change datetime to first day of the event
                'prototype_data' => new AccommodationOvernightCapacityView(new \DateTime(), 0),
            ])
        ;
    }

}
