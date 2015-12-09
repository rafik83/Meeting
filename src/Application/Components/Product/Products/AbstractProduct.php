<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product\Products;

use Proximum\Vimeet\Application\Components\Product\Including;
use Proximum\Vimeet\Application\Components\Product\Step;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProduct implements ProductInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Step
     */
    protected $step;

    /**
     * @var Including[]
     */
    protected $includedIn;

    /**
     * @var Including[]
     */
    protected $include;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefined([
            'includedIn',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function including(ProductInterface $includer, ProductInterface $include, $quantity)
    {
        return new Including($includer, $include, $quantity);
    }

    /**
     * {@inheritdoc}
     */
    public function addIncludedIn(Including $including)
    {
        $this->includedIn[] = $including;
    }

    /**
     * {@inheritdoc}
     */
    public function addInclude(Including $including)
    {
        $this->include[] = $including;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return array|null
     */
    public function getOptionsIncludedIn()
    {
        return isset($this->options['includedIn']) ? $this->options['includedIn'] : null;
    }

    /**
     * @return ProductInterface[]
     */
    public function getIncludedIn()
    {
        return $this->includedIn;
    }

    /**
     * @return ProductInterface[]
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @return float|null
     */
    public function getQuantityMin()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['min'])
            ? $this->options['quantity']['min'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantityMax()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['max'])
            ? $this->options['quantity']['max'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantityRange()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['range'])
            ? $this->options['quantity']['range'] : 1;
    }

    /**
     * {@inheritdoc}
     */
    public function setStep(Step $step)
    {
        $this->step = $step;
    }

    /**
     * {@inheritdoc}
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludingFromPurchase(array $packageData)
    {
        if ($this->getIncludedIn() === null) {
            return [];
        }

        $template   = $this->getStep()->getTemplate();
        $includings = [];

        if (!empty($packageData) && $template !== null) {
            foreach ($packageData as $stepKey => $stepData) {
                foreach ($stepData as $productKey => $productData) {
                    $toInclude = $this->isIncludedIn(
                        $this,
                        $template->getStep($stepKey)->getProduct($productKey),
                        $productData
                    );

                    if (!empty($toInclude)) {
                        $includings[] = $toInclude;
                    }
                }
            }
        }

        return $includings;
    }

    /**
     * @param ProductInterface $product
     * @param ProductInterface $productToCheck
     * @param array            $productData
     *
     * @return null|Including
     */
    private function isIncludedIn(ProductInterface $product, ProductInterface $productToCheck, array $productData)
    {
        if ($product->getIncludedIn() === null) {
            return null;
        }

        if ($productToCheck instanceof LibChoiceWithDescriptionProduct) {
            if (isset($productData['value'])) {
                $productChoice = $productToCheck->getChoice($productData['value']);
                if ($productChoice !== null) {
                    foreach ($product->getIncludedIn() as $includedIn) {
                        if ($productChoice === $includedIn->getProductThatInclude()) {
                            return $includedIn;
                        }
                    }
                }
            }
        } else {
            foreach ($product->getIncludedIn() as $includedIn) {
                if ($productToCheck === $includedIn->getProductThatInclude()) {
                    return $includedIn;
                }
            }
        }

        return null;
    }
}
