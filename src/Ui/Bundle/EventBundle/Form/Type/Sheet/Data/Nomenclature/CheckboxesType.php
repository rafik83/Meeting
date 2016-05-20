<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature\KeysToNomenclatureItemsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckboxesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new KeysToNomenclatureItemsTransformer($options['nomenclature']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'nomenclature']);
        $resolver->setDefaults([
            'choices'                   => function (Options $options) {
                return $options['nomenclature']->getLastLevel();
            },
            'expanded'                  => true,
            'multiple'                  => true,
            'choice_translation_domain' => false,
            'choices_as_values'         => true,
            'choice_name'               => function (NomenclatureItem $item) { return $item->getKey(); },
            'choice_value'              => function (NomenclatureItem $item) { return $item->getKey(); },
            'choice_label'              => function (Options $options) {
                return function (NomenclatureItem $item) use ($options) {
                    return $item->getLabel($options['locale']);
                };
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['locale']       = $options['locale'];
        $view->vars['nomenclature'] = $options['nomenclature'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_checkboxes';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
