<?php

namespace Proximum\Vimeet\Tests\Domain\Billing;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Billing\Prefiller;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class PrefillerTest extends TestCase
{
    public function testPrefillFromUser()
    {
        // Context
        $billingInfo = $this->prophesize(BillingInfo::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $billingInfo->getSheet()->willReturn($sheet->reveal());
        $sheet->getParticipantOwner()->willReturn(null);
        $account = $this->prophesize(User\Account::class);
        $user->getAccount()->willReturn($account->reveal());

        // Expected
        $user->getEmail()->shouldBeCalled()->willReturn('email@example.net');
        $sheet->getOwner()->shouldBeCalled()->willReturn($user->reveal());
        $account->getGender()->shouldBeCalled()->willReturn('Gender');
        $account->getFirstName()->shouldBeCalled()->willReturn('FirstName');
        $account->getLastName()->shouldBeCalled()->willReturn('LastName');
        $account->getPosition()->shouldBeCalled()->willReturn('Position');
        $account->getCompany()->shouldBeCalled()->willReturn('Company');
        $account->getPhone()->shouldBeCalled()->willReturn('Phone');
        $account->getMobile()->shouldBeCalled()->willReturn('Mobile');
        $account->getAddress()->shouldBeCalled()->willReturn('Address');
        $account->getZipCode()->shouldBeCalled()->willReturn('ZipCode');
        $account->getCity()->shouldBeCalled()->willReturn('City');
        $account->getCountry()->shouldBeCalled()->willReturn('Country');
        $billingInfo->prefill(
            'Gender',
            'FirstName',
            'LastName',
            'Position',
            'Company',
            'Phone',
            'Mobile',
            'email@example.net',
            new Address(
                'Address',
                'ZipCode',
                'City',
                'Country'
            )
        )->shouldBeCalled();

        // Mock
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);

        // Prefiller
        $prefiller = new Prefiller($templateDataFactory->reveal());
        $prefiller->prefill($billingInfo->reveal());
    }

    public function testPrefillFromParticipant()
    {
        // Context
        $billingInfo = $this->prophesize(BillingInfo::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $templateData = $this->prophesize(TemplateData::class);
        $event = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $event->getFallback()->willReturn('fr');
        $billingInfo->getSheet()->willReturn($sheet->reveal());
        $participant->getUser()->willReturn($user->reveal());
        $sheet->getParticipantOwner()->willReturn($participant->reveal());

        // Expected
        $user->getEmail()->shouldBeCalled()->willReturn('email@example.net');
        $templateData
            ->getTaggedContentLabel(Tag::PARTICIPANT_GENDER)
            ->shouldBeCalled()
            ->willReturn('PARTICIPANT_GENDER');
        $templateData
            ->getTaggedContentLabel(Tag::PARTICIPANT_FIRSTNAME)
            ->shouldBeCalled()
            ->willReturn('PARTICIPANT_FIRSTNAME');
        $templateData
            ->getTaggedContentLabel(Tag::PARTICIPANT_LASTNAME)
            ->shouldBeCalled()
            ->willReturn('PARTICIPANT_LASTNAME');
        $templateData
            ->getTaggedContentLabel(Tag::PARTICIPANT_POSITION)
            ->shouldBeCalled()
            ->willReturn('PARTICIPANT_POSITION');
        $templateData
            ->getTaggedContentLabel(Tag::SHEET_ORGANIZATION)
            ->shouldBeCalled()
            ->willReturn('SHEET_ORGANIZATION');
        $templateData
            ->getTaggedContentLabel(Tag::PARTICIPANT_PHONE)
            ->shouldBeCalled()
            ->willReturn('PARTICIPANT_PHONE');
        $templateData
            ->getTaggedContentLabel(Tag::PARTICIPANT_MOBILE)
            ->shouldBeCalled()
            ->willReturn('PARTICIPANT_MOBILE');
        $templateData->getTaggedContentLabel(Tag::SHEET_ADDRESS)->shouldBeCalled()->willReturn('SHEET_ADDRESS');
        $templateData->getTaggedContentLabel(Tag::SHEET_ZIPCODE)->shouldBeCalled()->willReturn('SHEET_ZIPCODE');
        $templateData->getTaggedContentLabel(Tag::SHEET_CITY)->shouldBeCalled()->willReturn('SHEET_CITY');
        $templateData->getTaggedContentValue(Tag::SHEET_COUNTRY)->shouldBeCalled()->willReturn('SHEET_COUNTRY');

        $billingInfo
            ->prefill(
                'PARTICIPANT_GENDER',
                'PARTICIPANT_FIRSTNAME',
                'PARTICIPANT_LASTNAME',
                'PARTICIPANT_POSITION',
                'SHEET_ORGANIZATION',
                'PARTICIPANT_PHONE',
                'PARTICIPANT_MOBILE',
                'email@example.net',
                new Address(
                    'SHEET_ADDRESS',
                    'SHEET_ZIPCODE',
                    'SHEET_CITY',
                    'SHEET_COUNTRY'
                )
            )->shouldBeCalled();

        // Mock
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory->createRegistrationFromParticipant($participant->reveal(), 'fr')->shouldBeCalled()->willReturn($templateData);

        // Prefiller
        $prefiller = new Prefiller($templateDataFactory->reveal());
        $prefiller->prefill($billingInfo->reveal());
    }
}
