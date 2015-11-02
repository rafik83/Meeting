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

class UploadWithChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fieldId = $options['fieldId'];
        $field   = $options['field'];
        $locale  = $options['locale'];

        $builder->add('upload_' . $fieldId, 'text', [
            'label'    => $field['label'][$locale],
            'required' => isset($field['required']) && $field['required'] === true,
        ]);

        $builder->add('options_' . $fieldId, new ChoiceType(), [
            'fieldId' => $fieldId,
            'field'   => $field,
            'locale'  => $locale,
            'label'   => false,
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
        return 'upload_with_choice';
    }
}
