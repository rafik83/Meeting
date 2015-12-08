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
            $builder->add($i, $field['type'], [
                'label'    => $field['label'][$locale],
                'help'     => isset($field['private']) && $field['private'] === true ? 'form.field.private' : null,
                'required' => isset($field['required']) && $field['required'] === true,
                'template' => $field,
                'locale'   => $locale,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
        $resolver->setAllowedTypes('template', ['array']);
        $resolver->setAllowedTypes('locale', ['string']);
    }
}
