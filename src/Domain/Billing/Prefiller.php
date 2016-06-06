<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Billing;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class Prefiller
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * Prefiller constructor.
     *
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param BillingInfo $billingInfo
     */
    public function prefill(BillingInfo $billingInfo)
    {
        $participant  = $billingInfo->getSheet()->getOwner();
        $locale       = $billingInfo->getSheet()->getEvent()->getFallback();
        $templateData = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

        $billingInfo->prefill(
            $templateData->getTaggedContentValue(Tag::PARTICIPANT_FIRSTNAME),
            $templateData->getTaggedContentValue(Tag::PARTICIPANT_LASTNAME),
            $templateData->getTaggedContentValue(Tag::SHEET_ORGANIZATION),
            $templateData->getTaggedContentValue(Tag::PARTICIPANT_PHONE),
            $templateData->getTaggedContentValue(Tag::PARTICIPANT_MOBILE),
            $participant->getUser()->getEmail(),
            new Address(
                $templateData->getTaggedContentValue(Tag::PARTICIPANT_ADDRESS),
                $templateData->getTaggedContentValue(Tag::PARTICIPANT_ZIPCODE),
                $templateData->getTaggedContentValue(Tag::PARTICIPANT_CITY),
                $templateData->getTaggedContentValue(Tag::PARTICIPANT_COUNTRY)
            )
        );
    }
}
