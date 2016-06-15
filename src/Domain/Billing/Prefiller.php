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
        /*
         * @todo Get the info from the user if no participant owner
         */
        $participant  = $billingInfo->getSheet()->getParticipantOwner();
        $locale       = $billingInfo->getSheet()->getEvent()->getFallback();
        $templateData = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

        $billingInfo->prefill(
            $templateData->getTaggedContentLabel(Tag::PARTICIPANT_FIRSTNAME),
            $templateData->getTaggedContentLabel(Tag::PARTICIPANT_LASTNAME),
            $templateData->getTaggedContentLabel(Tag::PARTICIPANT_POSITION),
            $templateData->getTaggedContentLabel(Tag::SHEET_ORGANIZATION),
            $templateData->getTaggedContentLabel(Tag::PARTICIPANT_PHONE),
            $templateData->getTaggedContentLabel(Tag::PARTICIPANT_MOBILE),
            $participant->getUser()->getEmail(),
            new Address(
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_ADDRESS),
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_ZIPCODE),
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_CITY),
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_COUNTRY)
            )
        );
    }
}
