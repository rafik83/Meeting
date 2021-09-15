<?php

namespace Proximum\Vimeet\Tests\Domain\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\UserToParticipant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class UserToParticipantTest extends TestCase
{
    public function testHandle()
    {
        $participantRepositoryInterfaceMock = $this->prophesize(ParticipantRepositoryInterface::class);
        $templateDataFactoryMock = $this->prophesize(TemplateDataFactory::class);

        $eventMock = $this->prophesize(Event::class);
        $eventMock->getFallback()->shouldBeCalled()->willReturn('fr');

        $typeMock = $this->prophesize(Type::class);
        $sheetMock = $this->prophesize(Sheet::class);
        $sheetMock->getType()->shouldBeCalled()->willReturn($typeMock->reveal());
        $sheetMock->getEvent()->shouldBeCalled()->willReturn($eventMock->reveal());

        $templateDataMock = $this->prophesize(TemplateData::class);
        $templateDataFactoryMock
            ->createRegistrationFromType($typeMock->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateDataMock->reveal());

        $userMock = $this->prophesize(User::class);
        $userMock->getGender()->shouldBeCalled()->willReturn('man');
        $userMock->getFirstName()->shouldBeCalled()->willReturn('Philippe');
        $userMock->getLastName()->shouldBeCalled()->willReturn('Gildas');
        $userMock->getAvatar()->shouldBeCalled()->willReturn('gildas.jpg');
        $userMock->getPosition()->shouldBeCalled()->willReturn('Présentateur TV');
        $userMock->getPhone()->shouldBeCalled()->willReturn('+33122334455');
        $userMock->getMobile()->shouldBeCalled()->willReturn('+3361122334455');
        $userMock->getAddress()->shouldBeCalled()->willReturn('20 rue de la Chaumière');
        $userMock->getZipCode()->shouldBeCalled()->willReturn('75001');
        $userMock->getCity()->shouldBeCalled()->willReturn('Paris');
        $userMock->getCountry()->shouldBeCalled()->willReturn('FR');
        $userMock->getWebsite()->shouldBeCalled()->willReturn('http://perdu.com');

        $templateDataMock
            ->setTaggedData(
                [
                    Tag::PARTICIPANT_GENDER    => 'man',
                    Tag::PARTICIPANT_FIRSTNAME => 'Philippe',
                    Tag::PARTICIPANT_LASTNAME  => 'Gildas',
                    Tag::PARTICIPANT_AVATAR    => 'gildas.jpg',
                    Tag::PARTICIPANT_POSITION  => 'Présentateur TV',
                    Tag::PARTICIPANT_PHONE     => '+33122334455',
                    Tag::PARTICIPANT_MOBILE    => '+3361122334455',
                    Tag::PARTICIPANT_ADDRESS   => '20 rue de la Chaumière',
                    Tag::PARTICIPANT_ZIPCODE   => '75001',
                    Tag::PARTICIPANT_CITY      => 'Paris',
                    Tag::PARTICIPANT_COUNTRY   => 'FR',
                    Tag::PARTICIPANT_WEBSITE   => 'http://perdu.com',
                ]
            )
            ->shouldBeCalled();

        $templateDataMock->getData()->shouldBeCalled()->willReturn(['data' => 'whatever']);

        $date = new \DateTime();
        $participant = new Participant($sheetMock->reveal(), $userMock->reveal(), ['data' => 'whatever'], true, $date);
        $participantRepositoryInterfaceMock->add($participant)->shouldBeCalled();
        $sheetMock->addParticipant($participant)->shouldBeCalled();

        $userToParticipant = new UserToParticipant(
            $participantRepositoryInterfaceMock->reveal(),
            $templateDataFactoryMock->reveal(),
            $date
        );

        $expectedParticipant = new Participant($sheetMock->reveal(), $userMock->reveal(), ['data' => 'whatever'], true, $date);
        $result = $userToParticipant->handle($sheetMock->reveal(), $userMock->reveal());

        $this->assertEquals($expectedParticipant, $result);
    }
}
