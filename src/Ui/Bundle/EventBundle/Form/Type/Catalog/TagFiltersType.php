<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Catalog;

use Proximum\Vimeet\Application\View\Catalog\Aggregat\NomenclatureTagView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            $choices = $options['taggedNomenclatureTagViews'][$tagFilterView->tag] ?? [];

            $builder->add($tagFilterView->tag, ChoiceType::class, [
                'label' => $tagFilterView->label,
                'choice_value' => function (NomenclatureTagView $nomenclatureTagView = null) {
                    if (null !== $nomenclatureTagView) {
                        return $nomenclatureTagView->key;
                    }

                    return null;
                },
                'choice_label' => function (NomenclatureTagView $nomenclatureTagView = null) {
                    if (null !== $nomenclatureTagView) {
                        return $nomenclatureTagView->title;
                    }

                    return null;
                },
                'choices' => $choices,
                'required' => false,
                'multiple' => true,
                'attr'  => [
                    'data-placeholder' => $tagFilterView->placeholder,
                    'class' => 'form-control select2',
                    'data-disallow-clear' => 'true',
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
            ->setRequired([
                'tagFilterViews',
                'taggedNomenclatureTagViews'
            ])
            ->setAllowedTypes('tagFilterViews', 'array')
            ->setAllowedTypes('taggedNomenclatureTagViews', 'array')
        ;
    }
}
