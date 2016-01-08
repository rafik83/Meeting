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
use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductBuilder
{
    /**
     * @var Type
     */
    private $type = null;

    /**
     * @param Sheet $sheet
     *
     * @return Template
     */
    public function createFromSheet(Sheet $sheet)
    {
        $this->type = $sheet->getType();
        return $this->create($sheet->getTypePackageTemplate());
    }

    /**
     * @param Type $type
     *
     * @return Template
     */
    public function createFromType(Type $type)
    {
        $this->type = $type;
        return $this->create($type->getPackageTemplate());
    }

    /**
     * @param Type $type
     * @param Cart $cart
     *
     * @return Template
     */
    public function createFromCart(Type $type, Cart $cart)
    {
        $this->type = $type;
        return $this->create($cart->getTemplate());
    }

    /**
     * @param Type $type
     * @param Order $order
     *
     * @return Template
     */
    public function createFromOrder(Type $type, Order $order)
    {
        $this->type = $type;
        return $this->create($order->getPackageTemplate());
    }

    /**
     * @param array $packageTemplate
     *
     * @return Template
     */
    private function create(array $packageTemplate)
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
                        $product = $this->createProduct($productTemplate, $productKey);

                        $step->addProduct($product);
                    }
                }
            }

            if (!empty($step->getProducts())) {
                $template->addStep($step);
            }
        }

        $template = $this->includeTheProducts($template);

        return $template;
    }

    /**
     * Create a product from the productTemplate and the key
     *
     * @param array  $productTemplate
     * @param string $productKey
     *
     * @return ProductInterface
     */
    private function createProduct(array $productTemplate, $productKey)
    {
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

        if (null !== $this->type && $product instanceof  LibParticipantProduct) {
            $product->setMaxParticipant($this->type->getMaxParticipant());
            $product->setFreeParticipant($this->type->getFreeParticipant());
        }

        if (null !== $this->type && $product instanceof  LibPlanningProduct) {
            $product->setMaxPlaning($this->type->getMaxPlanning());
        }

        return $product;
    }

    /**
     * @param Template $template
     *
     * @return Template
     */
    private function includeTheProducts(Template $template)
    {
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
    private function factory($type, $key)
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
