<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefaults([
           'allow_add'      => true,
           'allow_delete'   => true,
           'prototype_name' => '__option__',
           'entry_type'    => ProductChoiceType::class,
           'entry_options' => function (Options $options) {
               return [
                   'label'            => false,
                   'event'            => $options['event'],
                   'repositoryMethod' => function (ProductRepositoryInterface $productRepository) use ($options) {
                       return $productRepository->findByEventAndTypes($options['event'], [Product::TYPE_OPTION]);
                   },
               ];
           }
       ]);
    }

    public function getBlockPrefix()
    {
        return 'dsfmljdsmlkjflmkhdflkjsdhglkjsdghgkljdfh';
    }

    public function getParent()
    {
        return CollectionType::class;
    }
}
