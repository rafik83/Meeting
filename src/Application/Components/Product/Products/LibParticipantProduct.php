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

class LibParticipantProduct extends AbstractProduct
{
    /**
     * @var int
     */
    private $maxParticipant = 0;

    /**
     * @var int
     */
    private $freeParticipant = 0;

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
     * @param string $locale
     *
     * @return string|null
     */
    public function getDescription($locale)
    {
        return isset($this->options['description'][$locale]) ? $this->options['description'][$locale] : null;
    }

    /**
     * @return string
     */
    public function getRequired()
    {
        return $this->options['required'];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->options['unitPrice'];
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
