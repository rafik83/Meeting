<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\Object;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NomenclatureDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $nomenclature = $options['object'];
        $locale       = $options['locale'];
        $label        = $options['label'];

        $choices = $nomenclature->getNomenclatureLabels();

        if (null !== $choices) {

            if (!is_array(reset($choices))) {
                $choices = array_flip($choices);
            } else {
                $choices = array_map(
                    function ($values) {
                        return array_flip($values);
                    },
                    $choices
                );
            }

            $builder
                ->add('item', ChoiceType::class, [
                    'label'       => $label ? $nomenclature->getOption('label')[$locale] : false,
                    'required'    => $nomenclature->getOption('required'),
                    'choices'     => $choices,
                    'placeholder' => $nomenclature->getOption('label')[$locale],
                    'attr'        => [
                        'class'            => 'form-control select2',
                        'data-placeholder' => $nomenclature->getOption('label')[$locale],
                    ],
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', Object\Nomenclature::class);
        $resolver->setDefaults([
            'label'      => false,
            'data_class' => Object\Nomenclature::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_data';
    }
}
