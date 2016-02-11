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
use Proximum\Vimeet\Domain\Model\BillingInfoInterface;

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
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getName(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_NAME
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getAddress(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_ADDRESS
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getCity(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_CITY
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getZipcode(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_ZIPCODE
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getCountry(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_COUNTRY
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getPhone(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_PHONE
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getEmail(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_EMAIL
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getOrganization(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_ORGANIZATION
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getVatNumber(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_VAT_NUMBER
        );
    }

    /**
     * @param BillingInfoInterface $object
     *
     * @return string
     */
    public function getExtra(BillingInfoInterface $object)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            Tag::BILLING_EXTRA
        );
    }
}
