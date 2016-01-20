<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type;

use Proximum\Vimeet\Application\Exception\Data\DataTypeNotFoundException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $cart     = $options['cart'];
        $step     = $options['step'];
        $product  = null;

        foreach ($template as $i => $field) {
            if (!isset($this->types[$field['type']])) {
                throw new DataTypeNotFoundException('Type not found.');
            }

            if ($step !== null) {
                $product = $step->getProduct($i);
            }

            $builder->add($i, $this->types[$field['type']], [
                'label'    => $field['label'][$locale],
                'help'     => isset($field['private']) && $field['private'] === true ? 'form.field.private' : null,
                'required' => isset($field['required']) && $field['required'] === true,
                'template' => $field,
                'locale'   => $locale,
                'sheet'    => $sheet,
                'cart'     => $cart,
                'product'  => $product,
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
        $resolver->setDefaults(['cart' => null]);
        $resolver->setDefaults(['step' => null]);
    }
}
