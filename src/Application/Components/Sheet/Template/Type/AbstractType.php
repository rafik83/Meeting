<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template\Type;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownOptionException;
use Proximum\Vimeet\Application\Components\Sheet\Template\TypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType implements TypeInterface
{
    const MARK_AS_PARTICIPANT_FIRSTNAME = 'participant_firstname';
    const MARK_AS_PARTICIPANT_LASTNAME  = 'participant_lastname';
    const MARK_AS_BILLING_ADDRESS       = 'billing_address';
    const MARK_AS_BILLING_CITY          = 'billing_city';
    const MARK_AS_BILLING_ZIPCODE       = 'billing_zipcode';
    const MARK_AS_BILLING_COUNTRY       = 'billing_contry';
    const MARK_AS_BILLING_PHONE         = 'billing_phone';
    const MARK_AS_BILLING_EMAIL         = 'billing_email';
    const MARK_AS_BILLING_ORGANIZATION  = 'billing_organization';
    const MARK_AS_BILLING_VAT_NUMBER    = 'billing_vat_number';
    const MARK_AS_BILLING_EXTRA         = 'billing_extra';
    const MARK_AS_ORGANIZATION          = 'organization';

    /**
     * @var array
     */
    protected $options = [];

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['label', 'type']);
        $optionsResolver->setDefaults(['required' => false]);
        $optionsResolver->setDefined(['description', 'mark_as']);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $option
     *
     * @return mixed
     * @throws UnknownOptionException
     */
    protected function getOption($option)
    {
        if (!isset($this->options[$option])) {
            throw new UnknownOptionException($option, array_keys($this->options));
        }

        return $this->options[$option];
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel($locale)
    {
        $label = $this->getOption('label');

        return (string) (is_array($label) ? $label[$locale] : $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($locale)
    {
        $description = $this->getOption('description');

        return (string) (is_array($description) ? $description[$locale] : $description);
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return (bool) $this->getOption('required');
    }
}
