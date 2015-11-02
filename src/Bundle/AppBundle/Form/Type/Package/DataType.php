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
            $type = $field['type'];

            if ('uploadWithChoices' === $type) {
                $builder->add($i, new UploadWithChoiceType(), [
                    'fieldId' => $i,
                    'field'   => $field,
                    'locale'  => $locale,
                    'label'   => false,
                ]);
            } elseif ('radio' === $type) {
                $builder->add($i, new ChoiceType(), [
                    'fieldId' => $i,
                    'field'   => $field,
                    'locale'  => $locale,
                    'label'   => false,
                ]);
            } else {
                $builder->add($i, $type, [
                    'label'    => $field['label'][$locale],
                    'required' => isset($field['required']) && $field['required'] === true,
                ]);
            }
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
        return 'package';
    }
}
