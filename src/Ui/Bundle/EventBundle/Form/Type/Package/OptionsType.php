<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];

        $products = $sheet->getPackage()->getAvailablesOptions();
        
        foreach ($products as $product) {
            $builder->add(
                $product->getId(),
                QuantityType::class,
                [
                    'label'      => false,
                    'max'        => $product->getQuantityMax(),
                    'minMessage' => 'package.product.quantityMin',
                    'maxMessage' => 'package.product.quantityMax',
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['sheet']);
        $optionsResolver->addAllowedTypes('sheet', Sheet::class);
        $optionsResolver->setDefaults(
            [
                'data_class' => SelectOptions::class,
            ]
        );
    }
}
