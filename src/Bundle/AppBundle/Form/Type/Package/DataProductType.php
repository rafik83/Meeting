<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Components\Product\Products\LibParticipantProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataProductType extends AbstractType
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

        $productData = $sheet->getPackageData();

        foreach ($step->getProducts() as $product) {
            if ($product->isAvailableToPurchase(
                    $productData,
                    isset($productData[$step->getKey()][$product->getKey()])
                        ? $productData[$step->getKey()][$product->getKey()] : []
            )) {
                $builder->add($product->getKey(), $this->types[$product->getType()], [
                    'template' => $template[$product->getKey()],
                    'label'    => $product->getLabel($locale),
                    'required' => $product->getRequired(),
                    'locale'   => $locale,
                    'sheet'    => $sheet,
                    'cart'     => $cart,
                    'product'  => $product,
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
        $resolver->setAllowedTypes('template', ['array']);
        $resolver->setAllowedTypes('locale', ['string']);
        $resolver->setDefaults(['sheet' => null]);
        $resolver->setDefaults(['cart' => null]);
        $resolver->setDefaults(['step' => null]);
    }
}
