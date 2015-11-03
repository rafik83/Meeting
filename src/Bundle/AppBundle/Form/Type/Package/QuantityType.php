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

        if ('uploadWithChoices' === $type) {
            $builder->add($key, new UploadWithChoiceType(), [
                'fieldId' => $key,
                'field'   => $field,
                'locale'  => $locale,
                'label'   => false,
            ]);
        } elseif ('radio' === $type) {
            $builder->add($key, new ChoiceType(), [
                'fieldId' => $key,
                'field'   => $field,
                'locale'  => $locale,
                'label'   => false,
            ]);
        } else {
            $builder->add($key, $type, [
                'label'    => $field['label'][$locale],
                'required' => isset($field['required']) && $field['required'] === true,
            ]);
        }

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
