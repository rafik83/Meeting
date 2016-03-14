<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ids', ChoiceType::class, [
                'choices'           => $options['ids'],
                'choices_as_values' => true,
                'choice_name'       => function ($id) { return $id; },
                'expanded'          => true,
                'multiple'          => true,
            ])
            ->add('follower', FollowerChoiceType::class, [
                'event'       => $options['event'],
                'placeholder' => '',
            ])
            ->add('validate', SubmitType::class)
            ->add('assign', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['ids', 'event']);
        $resolver->setAllowedTypes('ids', ['array']);
    }
}
