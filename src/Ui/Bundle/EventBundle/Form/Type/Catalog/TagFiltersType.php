<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagFiltersType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tagFilterViews = $options['tagFilterViews'];

        foreach ($tagFilterViews as $tagFilterView) {
            $builder->add($tagFilterView->tag, TagChoiceType::class, [
                'label' => $tagFilterView->label,
                'attr'  => [
                    'data-placeholder' => $tagFilterView->placeholder,
                ],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('tagFilterViews')
            ->setAllowedTypes('tagFilterViews', 'array')
        ;
    }
}
