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

class LibParticipantProduct extends AbstractRequiredDescriptionTypeUnitPriceOptions
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
     * @param string $locale
     *
     * @return string|null
     */
    public function getLabel($locale)
    {
        return isset($this->options['label'][$locale]) ? $this->options['label'][$locale] : null;
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
}
