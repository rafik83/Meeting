<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Event\Participant\AddFastCheckin;
use Proximum\Vimeet\Application\Command\Event\Participant\AddFastCheckinHandler;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\SendActivateAccountFromLoginTokenHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\TemplateData\ParticipationTypeTemplateDataGetter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;

class AddFastCheckinHandlerTest extends TestCase
{
    public function testWithEmail(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $event->getFallback()->willReturn('fr');
        $type = $this->prophesize(Type::class);
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($user->reveal());

        // prophecy dependencies
        $convertToParticipantHandler = $this->prophesize(ConvertToParticipantHandler::class);
        $participationTypeTemplateDataGetter = $this->prophesize(ParticipationTypeTemplateDataGetter::class);
        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $registrationTemplateData = $this->prophesize(TemplateData::class);
        $sheetTemplateData = $this->prophesize(TemplateData::class);

        $sendActivateAccountFromLoginTokenHandler = $this->prophesize(SendActivateAccountFromLoginTokenHandler::class);

        $convertToParticipantHandler->handle(Argument::any())->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;
        $participationTypeTemplateDataGetter->getRegistrationTemplateDataByType($type->reveal())
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData->reveal())
        ;
        $participationTypeTemplateDataGetter->getSheetTemplateDataByType($type->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData->reveal())
        ;
        $delayedEventDispatcher->dispatch(Events::USER_PROFILE_COMPLETED, Argument::any())
            ->shouldBeCalled()
        ;

        // run test
        $query = new AddFastCheckin($event->reveal(), 'sonia.bompastor@sport.fr', $user->reveal());
        $query->type = $type->reveal();
        $query->hasAccessToMeetings = false;

        $handler = new AddFastCheckinHandler(
            $convertToParticipantHandler->reveal(),
            $participationTypeTemplateDataGetter->reveal(),
            $delayedEventDispatcher->reveal(),
            $sendActivateAccountFromLoginTokenHandler->reveal()
        );
        $handler->handle($query);
    }

    public function testWithoutEmail(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $event->getDomain()->willReturn('foo.bar');
        $event->getFallback()->willReturn('fr');
        $type = $this->prophesize(Type::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($user->reveal());
        $participant->getSheet()->willReturn($sheet->reveal());

        // prophecy dependencies
        $convertToParticipantHandler = $this->prophesize(ConvertToParticipantHandler::class);
        $participationTypeTemplateDataGetter = $this->prophesize(ParticipationTypeTemplateDataGetter::class);
        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $registrationTemplateData = $this->prophesize(TemplateData::class);
        $sheetTemplateData = $this->prophesize(TemplateData::class);

        $sendActivateAccountFromLoginTokenHandler = $this->prophesize(SendActivateAccountFromLoginTokenHandler::class);

        $convertToParticipantHandler->handle(Argument::any())->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;
        $participationTypeTemplateDataGetter->getRegistrationTemplateDataByType($type->reveal())
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData->reveal())
        ;
        $participationTypeTemplateDataGetter->getSheetTemplateDataByType($type->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData->reveal())
        ;
        $delayedEventDispatcher->dispatch(Events::USER_PROFILE_COMPLETED, Argument::any())
            ->shouldNotBeCalled()
        ;

        // run tests
        $query = new AddFastCheckin($event->reveal(), '', null);
        $query->type = $type->reveal();
        $query->hasAccessToMeetings = false;

        $handler = new AddFastCheckinHandler(
            $convertToParticipantHandler->reveal(),
            $participationTypeTemplateDataGetter->reveal(),
            $delayedEventDispatcher->reveal(),
            $sendActivateAccountFromLoginTokenHandler->reveal()
        );
        $handler->handle($query);
    }
}
