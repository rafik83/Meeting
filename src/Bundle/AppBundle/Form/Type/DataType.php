<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];

        foreach ($template as $i => $field) {
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

            $builder->add($i, $type, array_merge($options, [
                'label'    => $field['label'][$locale],
                'help'     => isset($field['private']) && $field['private'] === true ? 'form.field.private' : null,
                'required' => isset($field['required']) && $field['required'] === true,
            ]));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'data';
    }
}
