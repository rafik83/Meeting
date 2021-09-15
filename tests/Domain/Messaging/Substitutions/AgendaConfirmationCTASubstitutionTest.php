<?php

namespace Proximum\Vimeet\Tests\Domain\Messaging\Substitutions;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Adapter\TemplatingAdapterInterface;
use Proximum\Vimeet\Domain\Messaging\Substitutions\AgendaConfirmationCTASubstitution;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Token\UserEventTokenGenerator;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class AgendaConfirmationCTASubstitutionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $templating;

    /** @var ObjectProphecy */
    private $userEventTokenGenerator;

    /** @var ObjectProphecy */
    private $eventUrlGenerator;

    public function setUp()
    {
        $this->templating = $this->prophesize(TemplatingAdapterInterface::class);
        $this->eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
        $this->userEventTokenGenerator = $this->prophesize(UserEventTokenGenerator::class);
    }

    public function testGetCTAForBillingInfo()
    {
        $recipient = $this->prophesize(BillingInfo::class);
        $sheet = $this->prophesize(Sheet::class);

        $agendaConfirmationCTASubstitution = new AgendaConfirmationCTASubstitution(
            $this->templating->reveal(),
            $this->eventUrlGenerator->reveal(),
            $this->userEventTokenGenerator->reveal()
        );

        $result = $agendaConfirmationCTASubstitution->getCTA($recipient->reveal(), $sheet->reveal(), 'fr');

        $this->assertEquals('', $result);
    }

    public function testGetCTAForUserNotParticipant()
    {
        $recipient = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getParticipantOwner()->shouldBeCalled()->willReturn(null);

        $agendaConfirmationCTASubstitution = new AgendaConfirmationCTASubstitution(
            $this->templating->reveal(),
            $this->eventUrlGenerator->reveal(),
            $this->userEventTokenGenerator->reveal()
        );

        $result = $agendaConfirmationCTASubstitution->getCTA($recipient->reveal(), $sheet->reveal(), 'fr');

        $this->assertEquals('', $result);
    }

    public function testGetCTAForParticipant()
    {
        $recipient = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $recipient->getUser()->shouldBeCalled()->willReturn($user->reveal());
        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->getToken()->shouldBeCalled()->willReturn('token');

        $agendaConfirmationCTASubstitution = new AgendaConfirmationCTASubstitution(
            $this->templating->reveal(),
            $this->eventUrlGenerator->reveal(),
            $this->userEventTokenGenerator->reveal()
        );

        $this->eventUrlGenerator
            ->generateEventAbsoluteUrl(
                $event->reveal(),
                AgendaConfirmationCTASubstitution::ROUTE_CTA_AGENDA_CONFIRMATION,
                [
                    'token' => 'token',
                    '_locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn('link');

        $this->templating
            ->render(
                AgendaConfirmationCTASubstitution::TEMPLATING_CTA_AGENDA_CONFIRMATION,
                [
                    'link'   => 'link',
                    'locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn('<div>button</div>');

        $this->userEventTokenGenerator
            ->getUserEventTokenForConfirmAgenda(
                $event->reveal(),
                $user->reveal(),
                UserEventTokenType::AGENDA_CONFIRMATION
            )
            ->shouldBeCalled()
            ->willReturn($userEventToken->reveal());

        $result = $agendaConfirmationCTASubstitution->getCTA($recipient->reveal(), $sheet->reveal(), 'fr');
        $expected = '<div>button</div>';

        $this->assertEquals($expected, $result);
    }

    public function testGetCTAForUserOwnerParticipant()
    {
        $recipient = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);
        $sheet->getEvent()->willReturn($event->reveal());
        $participant = $this->prophesize(Participant::class);
        $sheet->getParticipantOwner()->willReturn($participant);
        $userEventToken = $this->prophesize(UserEventToken::class);
        $userEventToken->getToken()->shouldBeCalled()->willReturn('token');

        $agendaConfirmationCTASubstitution = new AgendaConfirmationCTASubstitution(
            $this->templating->reveal(),
            $this->eventUrlGenerator->reveal(),
            $this->userEventTokenGenerator->reveal()
        );

        $this->eventUrlGenerator
            ->generateEventAbsoluteUrl(
                $event->reveal(),
                AgendaConfirmationCTASubstitution::ROUTE_CTA_AGENDA_CONFIRMATION,
                [
                    'token' => 'token',
                    '_locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn('link');

        $this->templating
            ->render(
                AgendaConfirmationCTASubstitution::TEMPLATING_CTA_AGENDA_CONFIRMATION,
                [
                    'link' => 'link',
                    'locale' => 'fr',
                ]
            )
            ->shouldBeCalled()
            ->willReturn('<div>button</div>');

        $this->userEventTokenGenerator
            ->getUserEventTokenForConfirmAgenda(
                $event->reveal(),
                $recipient->reveal(),
                UserEventTokenType::AGENDA_CONFIRMATION
            )
            ->shouldBeCalled()
            ->willReturn($userEventToken->reveal());

        $result = $agendaConfirmationCTASubstitution->getCTA($recipient->reveal(), $sheet->reveal(), 'fr');
        $expected = '<div>button</div>';

        $this->assertEquals($expected, $result);
    }
}
