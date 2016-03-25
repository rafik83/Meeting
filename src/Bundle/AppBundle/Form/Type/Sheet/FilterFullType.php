<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\CategoryChoiceType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterFullType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('state', StateChoiceType::class, [
                'label'       => 'form.sheet_filter.children.state.label',
                'placeholder' => '',
            ])
            ->add('category', CategoryChoiceType::class, [
                'label'       => 'form.sheet_filter.children.category.label',
                'placeholder' => '',
                'event'       => $options['event'],
                'locale'      => $options['locale'],
            ])
            ->add('sheetName', SheetNameType::class, [
                'label' => 'form.sheet_filter.children.sheet_name.label',
            ])
            ->add('type', TypeChoiceType::class, [
                'label'       => 'form.sheet_filter.children.type.label',
                'placeholder' => '',
                'event'       => $options['event'],
                'locale'      => $options['locale'],
            ])
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
        return 'sheet_filter';
    }
}
