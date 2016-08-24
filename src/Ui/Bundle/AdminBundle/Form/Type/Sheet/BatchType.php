<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\Batch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                'choices'            => $options['ids'],
                'choice_name'        => function ($id) { return $id; },
                'expanded'           => true,
                'multiple'           => true,
                'label'              => false,
                'translation_domain' => false,
            ])
            ->add('follower', FollowerChoiceType::class, [
                'event'       => $options['event'],
                'placeholder' => '',
                'required'    => false,
            ])
            ->add('validateComment', TextareaType::class, [
                'required' => false,
            ])
            ->add('validate', SubmitType::class)
            ->add('assign', SubmitType::class)
            ->add('accept', SubmitType::class)
            ->add('enable', SubmitType::class)
            ->add('disable', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['ids', 'event']);
        $resolver->setAllowedTypes('ids', ['array']);
        $resolver->setDefaults(['data_class' => Batch::class]);
    }

    public function getBlockPrefix()
    {
        return 'sheet_batch';
    }
}
