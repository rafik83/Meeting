<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User;

use Proximum\Vimeet\Application\Command\User\Batch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ids', ChoiceType::class, [
                'choices' => $options['ids'],
                'choice_name' => function ($id) {
                    return $id;
                },
                'expanded' => true,
                'multiple' => true,
                'label' => false,
                'translation_domain' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['ids'])
            ->setAllowedTypes('ids', ['array'])
            ->setDefaults(['data_class' => Batch::class]);
    }
}
