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
        $this->key        = $key;
        $this->options    = [];
        $this->include    = [];
        $this->includedIn = [];
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['label']);
        $optionsResolver->setDefined([
            'includedIn',
            'required',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel($locale)
    {
        return isset($this->options['label'][$locale]) ? $this->options['label'][$locale] : null;
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
     * {@inheritdoc}
     */
    public function setOptions(array $options)
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
     * {@inheritdoc}
     */
    public function getIncludedIn()
    {
        return $this->includedIn;
    }

    /**
     * {@inheritdoc}
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @return bool
     */
    public function hasQuantity()
    {
        return $this->getQuantityMin() !== null
            && $this->getQuantityMax() !== null
            && $this->getQuantityMin() <= $this->getQuantityMax();
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
     * @param array $packageData
     *
     * @return bool
     */
    public function allowQuantity(array $packageData)
    {
        if (!$this->hasQuantity()) {
            return false;
        }

        $remaingQuantityMax = $this->getRemainingQuantityMax($packageData);

        if ($remaingQuantityMax > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $packageData
     * @return float
     */
    public function getRemainingQuantityMax(array $packageData)
    {
        if ($this->hasQuantity() === false) {
            return 0;
        }

        $quantityIncluded = $this->getQuantityIncludedWithPurchase($packageData);

        if (null === $quantityIncluded) {
            return 0;
        }

        $quantityMax    = $this->getQuantityMax();
        $quantityBought = $this->getQuantityBought($packageData);

        return ($quantityMax - $quantityBought - $quantityIncluded) > 0
            && ($quantityMax - $quantityBought - $quantityIncluded) >= $this->getQuantityMin()
            ? $quantityMax - $quantityBought- $quantityIncluded : 0;
    }

    /**
     * @param array $packageData
     *
     * @return int
     */
    public function getQuantityBought(array $packageData)
    {
        if (empty($packageData)) {
            return 0;
        }

        if (!isset($packageData[$this->getStep()->getKey()][$this->getKey()]['quantity'])) {
            return 0;
        } else {
            return $packageData[$this->getStep()->getKey()][$this->getKey()]['quantity'];
        }
    }

    /**
     * @param array $packageData
     *
     * @return int|null
     */
    public function getQuantityIncludedWithPurchase(array $packageData)
    {
        $quantity   = 0;
        $includings = $this->getIncludingFromPurchase($packageData);

        foreach ($includings as $including) {
            if ($including->getQuantity() === null) {
                return null;
            } else {
                $quantity += $including->getQuantity();
            }
        }

        return $quantity;
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
     * @return string
     */
    public function getRequired()
    {
        return isset($this->options['required']) ? $this->options['required'] : false;
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
                    if (null !== $template->getStep($stepKey)
                        && null !== $template->getStep($stepKey)->getProduct($productKey)
                    ) {
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
        }

        return $includings;
    }

    /**
     * Check if the given product is bought and included for a product
     * and return it
     *
     * @param ProductInterface $product
     * @param ProductInterface $productToCheck
     * @param array            $productData
     *
     * @return null|Including
     */
    private function isIncludedIn(ProductInterface $product, ProductInterface $productToCheck, array $productData)
    {
        if ($product->getIncludedIn() === null || !isset($productData['value'])) {
            return null;
        }

        // This use case is working for LibChoiceWithDescriptionProduct and LibOptionProduct
        // It doesn't take into account LibParticipant and LibPlanning
        if ($productToCheck instanceof LibChoiceWithDescriptionProduct) {
            $productChoice = $productToCheck->getChoice($productData['value']);
            if ($productChoice !== null) {
                foreach ($product->getIncludedIn() as $includedIn) {
                    if ($productChoice === $includedIn->getProductThatInclude()) {
                        return $includedIn;
                    }
                }
                return null;
            }
        }

        foreach ($product->getIncludedIn() as $includedIn) {
            if ($productToCheck === $includedIn->getProductThatInclude() && false !== $productData['value']) {
                return $includedIn;
            }
        }

        return null;
    }
}
