<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterPartType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', SheetTextSearchType::class, [
                'placeholder' => 'form.sheet_filter.children.text_search.label',
            ])
            ->add('enabled', HiddenType::class)
            ->add('state', HiddenType::class)
            ->add('completed', HiddenType::class)
            ->add('category', HiddenType::class)
            ->add('type', HiddenType::class)
            ->add('follower', HiddenType::class)
            ->add('predefined', HiddenType::class)
            ->add('validationState', HiddenType::class)
            ->add('order', HiddenType::class)
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_part_filter';
    }
}
