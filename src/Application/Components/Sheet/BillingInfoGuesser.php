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
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_NAME
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getAddress(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_ADDRESS
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getCity(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_CITY
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getZipcode(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_ZIPCODE
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getCountry(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_COUNTRY
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getPhone(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_PHONE
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getEmail(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_EMAIL
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getOrganization(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_ORGANIZATION
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getVatNumber(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_VAT_NUMBER
        );

        return !empty($info) ? $info[0] : null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getExtra(Sheet $sheet)
    {
        $info = $this->taggedInfoGuesser->guess(
            $sheet->getType()->getEvent()->getBillingTemplate(),
            $sheet->getBillingData(),
            Tag::BILLING_EXTRA
        );

        return !empty($info) ? $info[0] : null;
    }
}
