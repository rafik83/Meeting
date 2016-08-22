<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaCollectionDataType extends AbstractType
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var TemplateProductGuesser
     */
    private $templateProductGuesser;

    /**
     * ImageDataType constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param TemplateProductGuesser     $templateProductGuesser
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        TemplateProductGuesser $templateProductGuesser
    ) {
        $this->productRepository      = $productRepository;
        $this->templateProductGuesser = $templateProductGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MediaCollection $media */
        $media = $options['object'];

        $builder
            ->add('medias', CollectionType::class, [
                'entry_type'    => MediaDataType::class,
                'entry_options' => [
                    'label'       => false,
                    'collection'  => $options['data'],
                    'required'    => false,
                    'placeholder' => $options['placeholder'],
                ],
                'allow_add'     => true,
                'allow_delete'  => true,
                'label'         => false,
                'max'           => $options['data']->getMax(),
            ]);

        if ($this->templateProductGuesser->hasPayableOption($media)) {

            $selectedRadio = count($media->getBuyableProducts()) === 1 ?
                $media->getBuyableProducts()[0]->getId() :
                $media->getSelectedProduct();

            $builder
                ->add('selectedProduct', ChoiceType::class, [
                    'expanded'    => true,
                    'multiple'    => false,
                    'label'       => false,
                    'choice_name' => 'id',
                    'choices'     => $media->getBuyableProducts(),
                    'required'    => true,
                    'data'        => $selectedRadio,
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
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => MediaCollection::class,
            'placeholder' => null,
            'help'        => null,
            'attr'        => [
                'data-product-selector' => (int)true,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_media_collection_data';
    }
}
