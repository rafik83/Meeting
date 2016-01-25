<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order\Specification;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Satisfaction\CompositeSpecification;
use Proximum\Vimeet\Application\Components\Sheet\TaggedInfoGuesser;

class VatApplicable extends CompositeSpecification
{
    /**
     * @var TaggedInfoGuesser
     */
    private $taggedInfoGuesser;

    /**
     * @var array
     */
    private $europeanCountries;

    /**
     * VatApplicable constructor.
     *
     * @param TaggedInfoGuesser $taggedInfoGuesser
     * @param array             $europeanCountries
     */
    public function __construct(TaggedInfoGuesser $taggedInfoGuesser, array $europeanCountries)
    {
        $this->taggedInfoGuesser = $taggedInfoGuesser;
        $this->europeanCountries = $europeanCountries;
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy($object)
    {
        if (!$object instanceof Order) {
            return false;
        }

        if ($object->getVatMode() === Event::VAT_MODE_ATI) {
            return false;
        }

        $billingCountry = $this->taggedInfoGuesser->guessFromOrder($object, Tag::BILLING_COUNTRY)[0];
        $eventCountry   = $object->getSheet()->getEvent()->getPaymentAddress()->getCountry();

        // Order billing country and event country are the same
        if ($billingCountry === $eventCountry) {
            return true;
        }

        $vatNumber = $this->taggedInfoGuesser->guessFromOrder($object, Tag::BILLING_VAT_NUMBER)[0];

        // Order billing country is in the EU and there is not billing vat number
        if (in_array($billingCountry, $this->europeanCountries) && !$vatNumber) {
            return true;
        }

        return false;
    }
}
