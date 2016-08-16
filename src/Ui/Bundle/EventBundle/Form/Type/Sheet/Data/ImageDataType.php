<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ImageDataType extends AbstractType
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * ImageDataType constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TemplateObject\Image $image */
        $image = $options['object'];

        $builder->add('file', FileType::class, [
            'label'       => $options['label'],
            'required'    => $image->getOption('required'),
            'mapped'      => false,
            'attr'        => [
                'accept' => implode(', ', TemplateObject\Image::supportedMimeType()),
            ],
            'constraints' => [
                new Image(['mimeTypes' => TemplateObject\Image::supportedMimeType()]),
            ]
        ]);

        if (null !== $image->getBuyableProducts()) {

            $selectedRadio = count($image->getBuyableProducts()) === 1 ?
                $image->getBuyableProducts()[0]->getId() :
                $image->getSelectedProduct();

            $builder
                ->add('selectedProduct', ChoiceType::class, [
                    'expanded'    => true,
                    'multiple'    => false,
                    'label'       => false,
                    'choice_name' => 'id',
                    'choices'     => $image->getBuyableProducts(),
                    'required'    => true,
                    'data'        => $selectedRadio
                ]);

            $builder->get('selectedProduct')
                ->addModelTransformer(new IdToProductTransformer($this->productRepository));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\Image::class);
        $resolver->setDefaults([
            'label'       => false,
            'data_class'  => TemplateObject\Image::class,
            'placeholder' => null,
            'help'        => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_image_data';
    }
}
