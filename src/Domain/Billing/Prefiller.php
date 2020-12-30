<?php

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
        $participant  = $billingInfo->getSheet()->getParticipantOwner();

        if (null === $participant) {
            $this->prefillFromUser($billingInfo);
        } else {
            $locale       = $billingInfo->getSheet()->getEvent()->getFallback();
            $templateData = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

            $billingInfo->prefill(
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_GENDER),
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_FIRSTNAME),
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_LASTNAME),
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_POSITION),
                $templateData->getTaggedContentLabel(Tag::SHEET_ORGANIZATION),
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_PHONE),
                $templateData->getTaggedContentLabel(Tag::PARTICIPANT_MOBILE),
                $participant->getUser()->getEmail(),
                new Address(
                    $templateData->getTaggedContentLabel(Tag::SHEET_ADDRESS),
                    $templateData->getTaggedContentLabel(Tag::SHEET_ZIPCODE),
                    $templateData->getTaggedContentLabel(Tag::SHEET_CITY),
                    $templateData->getTaggedContentValue(Tag::SHEET_COUNTRY)
                )
            );
        }
    }

    /**
     * @param BillingInfo $billingInfo
     */
    private function prefillFromUser(BillingInfo $billingInfo)
    {
        $user = $billingInfo->getSheet()->getOwner();

        $billingInfo->prefill(
            $user->getAccount()->getGender(),
            $user->getAccount()->getFirstName(),
            $user->getAccount()->getLastName(),
            $user->getAccount()->getPosition(),
            $user->getAccount()->getCompany(),
            $user->getAccount()->getPhone(),
            $user->getAccount()->getMobile(),
            $user->getEmail(),
            new Address(
                $user->getAccount()->getAddress(),
                $user->getAccount()->getZipCode(),
                $user->getAccount()->getCity(),
                $user->getAccount()->getCountry()
            )
        );
    }
}
