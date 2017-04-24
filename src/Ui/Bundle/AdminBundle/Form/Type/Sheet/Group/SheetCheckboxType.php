<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group;

use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetCheckboxType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['sheetViews'])) {
            $builder->add('sheetViews', ChoiceType::class, [
                'choices' => $options['sheetViews'],
                'choice_label' => function (SheetView $sheetView) {
                    return $sheetView->title;
                },
                'choice_value' => function (SheetView $sheetView) {
                    return $sheetView->id;
                },
                'multiple' => true,
                'expanded' => true,
                'label' => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sheetViews']);
        $resolver->setAllowedTypes('sheetViews', 'array');
    }
}
