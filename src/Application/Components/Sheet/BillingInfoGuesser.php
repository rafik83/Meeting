<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;

class BillingInfoGuesser
{
    /**
     * @var TaggedInfoGuesser
     */
    private $taggedInfoGuesser;

    /**
     * BillingInfoGuesser constructor.
     *
     * @param TaggedInfoGuesser $taggedInfoGuesser
     */
    public function __construct(TaggedInfoGuesser $taggedInfoGuesser)
    {
        $this->taggedInfoGuesser = $taggedInfoGuesser;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getName(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_NAME
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getAddress(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_ADDRESS
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getCity(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_CITY
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getZipcode(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_ZIPCODE
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getCountry(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_COUNTRY
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getPhone(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_PHONE
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getEmail(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_EMAIL
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getOrganization(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_ORGANIZATION
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getVatNumber(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_VAT_NUMBER
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getExtra(Sheet $sheet)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_EXTRA
        );
    }
}
