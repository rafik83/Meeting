<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldId = $options['fieldId'];
        $field   = $options['field'];
        $locale  = $options['locale'];
        $choices = [];

        foreach ($field['choices'] as $choice) {
            $choices[] = $choice['label'][$locale];
        }

        $builder->add($fieldId, 'choice', [
            'choices'     => $choices,
            'expanded'    => true,
            'label'       => $field['label'][$locale],
            'placeholder' => false,
            'required'    => isset($field['required']) && $field['required'] === true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['fieldId', 'field', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'choice';
    }
}
