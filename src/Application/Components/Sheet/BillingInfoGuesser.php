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
use Proximum\Vimeet\Domain\Template\TaggedInfoGuesser;

class BillingInfoGuesser
{
    /**
     * @var TaggedInfoGuesser
     */
    private $taggedInfoGuesser;

    /**
     * @param TaggedInfoGuesser $taggedInfoGuesser
     */
    public function __construct(TaggedInfoGuesser $taggedInfoGuesser)
    {
        $this->taggedInfoGuesser = $taggedInfoGuesser;
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getName(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_NAME, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getAddress(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_ADDRESS, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getCity(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_CITY, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getZipcode(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_ZIPCODE, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getCountry(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_COUNTRY, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getPhone(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_PHONE, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getEmail(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_EMAIL, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getOrganization(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_ORGANIZATION, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getVatNumber(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_VAT_NUMBER, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $locale
     *
     * @return string
     */
    public function getExtra(BillingInfoInterface $object, $locale)
    {
        return $this->get($object, Tag::BILLING_EXTRA, $locale);
    }

    /**
     * @param BillingInfoInterface $object
     * @param string               $tag
     * @param string               $locale
     *
     * @return string
     */
    private function get(BillingInfoInterface $object, $tag, $locale)
    {
        return $this->taggedInfoGuesser->guessFirst(
            $object->getBillingTemplate(),
            $object->getBillingData(),
            $tag,
            $locale
        );
    }
}
