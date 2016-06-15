<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule;

use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateTimePickerType::class)
            ->add('end', DateTimePickerType::class)
            ->add('interval', IntegerType::class)
            ->add('duration', IntegerType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
            'empty_data' => function (FormInterface $form) {
                return new Recipe(
                    $form->get('begin')->getData(),
                    $form->get('end')->getData(),
                    $form->get('interval')->getData(),
                    $form->get('duration')->getData()
                );
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'generate_slot_recipe';
    }
}
