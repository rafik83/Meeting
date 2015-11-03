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

class QuantityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $field   = $options['field'];
        $key     = $options['key'];
        $locale  = $options['locale'];
        $type    = $field['type'];
        $options = [];

        if ('radio' === $type) {
            $type = 'choice';

            $choices = [];

            foreach ($field['choices'] as $choice) {
                $choices[] = $choice['label'][$locale];
            }

            $options = [
                'choices'  => $choices,
                'expanded' => true,
            ];
        }

        $builder->add($key, $type, array_merge($options, [
            'label'    => $field['label'][$locale],
            'help'     => isset($field['private']) && $field['private'] === true ? 'form.field.private' : null,
            'required' => isset($field['required']) && $field['required'] === true,
        ]));

        if (isset($field['quantity']['min'])
            && isset($field['quantity']['max'])
            && $field['quantity']['min'] < $field['quantity']['max']
        ) {
            $range = [];

            for ($i = $field['quantity']['min']; $i <= $field['quantity']['max']; ++$i) {
                $range[$i] = $i;
            }
            $builder->add('quantity', 'choice', [
                'choices' => $range,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['field', 'key', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'quantity';
    }
}
