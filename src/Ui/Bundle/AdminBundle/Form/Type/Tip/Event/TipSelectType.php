<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TipSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['tipViews']);
        $resolver->setAllowedTypes('tipViews', 'array');
        $resolver->setDefaults([
            'choice_label' => function (TipTranslationView $tipView) {
                return $tipView->title;
            },
            'choices' => function (Options $options) {
                return $options['tipViews'];
            }
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
