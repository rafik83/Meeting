<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class HappeningType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event = $options['event'];

        $builder
            ->add('category', CategoryType::class, ['event' => $event, 'locale' => $options['locale']])
            ->add('begin', DateTimeType::class, ['view_timezone' => $event->getTimeZone()])
            ->add('end', DateTimeType::class, ['view_timezone' => $event->getTimeZone()])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label'      => false,
            ])
            ->add('talkings', CollectionType::class, [
                'entry_type'     => TalkingType::class,
                'entry_options'  => ['label' => false],
                'prototype_data' => ['speaker' => null, 'position' => 0],
                'allow_add'      => true,
                'allow_delete'   => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'happening';
    }
}
