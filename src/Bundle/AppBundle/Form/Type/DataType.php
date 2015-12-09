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
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

class DataType extends AbstractType
{
    /**
     * @var array
     */
    private $types;

    /**
     * DataType constructor.
     *
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];
        $sheet    = $options['sheet'];

        foreach ($template as $i => $field) {
            if (!isset($this->types[$field['type']])) {
                throw new \RuntimeException('Type not found.');
            }

            $builder->add($i, $this->types[$field['type']], [
                'label'    => $field['label'][$locale],
                'help'     => isset($field['private']) && $field['private'] === true ? 'form.field.private' : null,
                'required' => isset($field['required']) && $field['required'] === true,
                'template' => $field,
                'locale'   => $locale,
                'sheet'    => $sheet,
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
        $resolver->setDefaults(['sheet' => null]);
    }
}
