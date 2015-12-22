<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product\Products;

use Symfony\Component\OptionsResolver\OptionsResolver;

class LibParticipantProduct extends AbstractDescriptionTypeUnitPriceOptions
{
    /**
     * @var int
     */
    private $maxParticipant;

    /**
     * @var int
     */
    private $freeParticipant;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        parent::__construct($key);

        $this->maxParticipant  = 0;
        $this->freeParticipant = 0;

    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $optionsResolver)
    {
        parent::configure($optionsResolver);

        $optionsResolver->setRequired(['label', 'type', 'required', 'unitPrice']);
        $optionsResolver->setDefined([
            'description',
        ]);
    }

    /**
     * @return int
     */
    public function getFreeParticipant()
    {
        return $this->freeParticipant;
    }

    /**
     * @param int $freeParticipant
     */
    public function setFreeParticipant($freeParticipant)
    {
        $this->freeParticipant = $freeParticipant;
    }

    /**
     * @return int
     */
    public function getMaxParticipant()
    {
        return $this->maxParticipant;
    }

    /**
     * @param int $maxParticipant
     */
    public function setMaxParticipant($maxParticipant)
    {
        $this->maxParticipant = $maxParticipant;
    }

    /**
     * @param array $packageData
     * @param array $data
     *
     * @return bool
     */
    public function isAvailableToPurchase(array $packageData, array $data)
    {
        if (empty($data) || !isset($data['participant']) || false === $data['participant']) {
            return true;
        }

        if ($this->hasQuantity() && $this->getRemainingQuantityMax($packageData) > 0) {
            return true;
        }

        return false;
    }
}
