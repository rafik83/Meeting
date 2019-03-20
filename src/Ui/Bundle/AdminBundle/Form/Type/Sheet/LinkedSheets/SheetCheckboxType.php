<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\LinkedSheets;

use Proximum\Vimeet\Application\View\Group\Sheet\SheetView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SheetCheckboxType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sheetViews']);
        $resolver->setAllowedTypes('sheetViews', 'array');
        $resolver->setDefaults([
            'choice_label' => function (SheetView $sheetView) {
                return $sheetView->title;
            },
            'choices' => function (Options $options) {
                return $options['sheetViews'];
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
