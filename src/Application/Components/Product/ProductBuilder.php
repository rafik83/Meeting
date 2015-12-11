<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product;

use Proximum\Vimeet\Application\Components\Product\Products\LibChoiceProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibChoiceWithDescriptionProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibOptionProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibParticipantProduct;
use Proximum\Vimeet\Application\Components\Product\Products\LibPlanningProduct;
use Proximum\Vimeet\Application\Components\Product\Products\ProductInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductBuilder
{
    /**
     * @param Sheet $sheet
     *
     * @return Template
     */
    public function createFromSheet(Sheet $sheet)
    {
        return $this->create($sheet->getTypePackageTemplate());
    }

    public function createFromType(Type $type)
    {
        return $this->create($type->getPackageTemplate());
    }

    /**
     * @param array $packageTemplate
     *
     * @return Template
     */
    public function create(array $packageTemplate)
    {
        $template = new Template();

        foreach ($packageTemplate as $stepKey => $stepTemplate) {
            $step = new Step($stepKey);

            $resolver = new OptionsResolver();
            $step->configure($resolver);
            $options = $resolver->resolve($stepTemplate);
            $step->setOptions($options);

            if (isset($stepTemplate['template'])) {
                foreach ($stepTemplate['template'] as $productKey => $productTemplate) {
                    if (isset($productTemplate['type'])) {
                        $product = $this->factory($productTemplate['type'], $productKey);

                        $resolver = new OptionsResolver();
                        $product->configure($resolver);
                        $options = $resolver->resolve($productTemplate);
                        $product->setOptions($options);

                        if ($product instanceof LibChoiceWithDescriptionProduct) {
                            foreach ($product->getOptionChoices() as $choiceKey => $choiceTemplate) {
                                $choiceProduct = new LibChoiceProduct($choiceKey);

                                $resolver = new OptionsResolver();
                                $choiceProduct->configure($resolver);
                                $options = $resolver->resolve($choiceTemplate);
                                $choiceProduct->setOptions($options);

                                $choiceProduct->setChoiceParent($product);
                                $product->addChoice($choiceProduct);
                            }
                        }

                        $step->addProduct($product);
                    }
                }
            }

            if (!empty($step->getProducts())) {
                $template->addStep($step);
            }
        }

        foreach ($template->getSteps() as $step) {
            foreach ($step->getProducts() as $product) {
                if ($product->getOptionsIncludedIn() === null) {
                    continue;
                }

                foreach ($product->getOptionsIncludedIn() as $productThatIncludekey => $includedQuantity) {
                    $path               = explode('.', $productThatIncludekey);
                    $productThatInclude = null;

                    if (count($path) === 3) {
                        $productThatInclude = $template->getStep($path[0])->getProduct($path[1])->getChoice($path[2]);
                    } else {
                        $productThatInclude = $template->getStep($path[0])->getProduct($path[1]);
                    }

                    if (null !== $productThatInclude) {
                        $product->including($productThatInclude, $product, $includedQuantity);
                    }
                }
            }
        }

        return $template;
    }

    /**
     * @param string $type
     * @param string $key
     *
     * @return ProductInterface
     */
    public function factory($type, $key)
    {
        $mapping = [
            'choice_with_description' => LibChoiceWithDescriptionProduct::class,
            'lib_option'              => LibOptionProduct::class,
            'lib_planning'            => LibPlanningProduct::class,
            'lib_participant'         => LibParticipantProduct::class,
        ];

        return new $mapping[$type]($key);
    }
}
