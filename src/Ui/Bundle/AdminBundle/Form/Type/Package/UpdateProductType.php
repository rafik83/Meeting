<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Exception\Data\DataTypeNotFoundException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Library\OptionEditType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateProductType extends AbstractType
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
        if (!isset($this->types[$options['template']['type']])) {
            throw new DataTypeNotFoundException(sprintf('Type %s not valid to edit.', $options['template']['type']));
        }

        $builder->add('productItem', OptionEditType::class, [
            'template' => $options['template'],
            'label'    => false,
            'locale'   => $options['locale'],
            'sheet'    => $options['sheet'],
            'product'  => $options['product'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
        $resolver->setAllowedTypes('template', ['array']);
        $resolver->setAllowedTypes('locale', ['string']);
        $resolver->setDefined(['sheet']);
        $resolver->setDefined(['product']);
    }
}
