<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
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
    /** @var IdToProductTransformer */
    private $idToProductTransformer;

    /** @var TemplateProductGuesser */
    private $templateProductGuesser;

    /** @var BuyableObjectResolver */
    private $buyableObjectResolver;

    /** @var TemplateObject\BuyableIncludedProductGuesser */
    private $buyableIncludedProductGuesser;

    /**
     * @param IdToProductTransformer                       $idToProductTransformer
     * @param TemplateProductGuesser                       $templateProductGuesser
     * @param BuyableObjectResolver                        $buyableObjectResolver
     * @param TemplateObject\BuyableIncludedProductGuesser $buyableIncludedProductGuesser
     */
    public function __construct(
        IdToProductTransformer $idToProductTransformer,
        TemplateProductGuesser $templateProductGuesser,
        BuyableObjectResolver $buyableObjectResolver,
        TemplateObject\BuyableIncludedProductGuesser $buyableIncludedProductGuesser
    ) {
        $this->idToProductTransformer = $idToProductTransformer;
        $this->templateProductGuesser = $templateProductGuesser;
        $this->buyableObjectResolver  = $buyableObjectResolver;
        $this->buyableIncludedProductGuesser = $buyableIncludedProductGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TemplateObject\Image $image */
        $image = $options['object'];

        $builder->add('file', FileType::class, [
            'label'       => true === $options['showLabel'] ? $image->getLabel($options['locale']) : false,
            'required'    => $image->getOption('required'),
            'attr'        => [
                'accept' => implode(', ', TemplateObject\Image::supportedMimeType()),
            ],
            'constraints' => [
                new Image(['mimeTypes' => TemplateObject\Image::supportedMimeType()]),
            ],
        ]);

        if ($this->templateProductGuesser->hasPayableOption($image)
            && !$this->buyableIncludedProductGuesser->hasBuyableIncludedProduct($image)
        ) {
            $selectedRadio = $this->buyableObjectResolver->getSelectedProduct($image);

            $builder->add('selectedProduct', ChoiceType::class, [
                'expanded'    => true,
                'multiple'    => false,
                'choice_name' => 'id',
                'choices'     => $image->getBuyableProducts(),
                'required'    => true,
                'data'        => $selectedRadio,
                'attr' => ['required' => true],
            ]);
            $builder->get('selectedProduct')->addModelTransformer($this->idToProductTransformer);
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
            'showLabel'   => false,
            'data_class'  => TemplateObject\Image::class,
            'placeholder' => null,
            'help'        => null,
            'attr'        => [
                'data-product-selector' => (int) true,
            ],
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
