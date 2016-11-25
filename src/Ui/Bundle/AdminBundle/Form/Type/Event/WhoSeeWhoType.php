<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\WhoType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WhoSeeWhoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $delegatedOptions = [
            'event'       => $options['event'],
            'locale'      => $options['locale'],
            'placeholder' => '',
        ];

        $builder
            ->add('seer', WhoType::class, $delegatedOptions)
            ->add('seeable', WhoType::class, $delegatedOptions)
            ->add('priority', IntegerType::class, [
                'attr'     =>  [
                    'min' => 0,
                    'max' => 10,
                ],
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
    }
}
