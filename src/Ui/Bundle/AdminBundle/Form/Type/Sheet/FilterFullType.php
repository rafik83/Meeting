<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\CategoryChoiceType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
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
            ->add('text', SheetTextSearchType::class, [
                'label' => 'form.sheet_filter.children.text_search.label',
            ])
            ->add('enabled', EnabledChoiceType::class, [
                'label'       => 'form.sheet_filter.children.enabled.label',
                'placeholder' => '',
            ])
            ->add('state', StateChoiceType::class, [
                'label'       => 'form.sheet_filter.children.state.label',
                'placeholder' => '',
            ])
            ->add('completed', CompletedChoiceType::class, [
                'label'       => 'form.sheet_filter.children.completed.label',
                'placeholder' => ''
            ])
            ->add('category', CategoryChoiceType::class, [
                'label'       => 'form.sheet_filter.children.category.label',
                'placeholder' => '',
                'event'       => $options['event'],
                'locale'      => $options['locale'],
            ])
            ->add('type', TypeChoiceType::class, [
                'label'       => 'form.sheet_filter.children.type.label',
                'placeholder' => '',
                'event'       => $options['event'],
                'locale'      => $options['locale'],
                'user'        => $options['user']
            ])
            ->add('follower', FollowerChoiceType::class, [
                'label'       => 'form.sheet_filter.children.follower.label',
                'placeholder' => '',
                'event'       => $options['event'],
            ])
            ->add('predefined', PredefinedFiltersChoiceType::class, [
                'label'       => 'form.filter.label',
                'placeholder' => '',
                'event'       => $options['event'],
            ])
            ->add('validationState', ValidationStateChoiceType::class, [
                'label'       => 'form.sheet_filter.children.validationState.label',
                'placeholder' => '',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'user']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_filter';
    }
}
