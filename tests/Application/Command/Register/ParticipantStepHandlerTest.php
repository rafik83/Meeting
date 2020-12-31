<?php

namespace Proximum\Vimeet\Tests\Application\Command\Register;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Register\ParticipantStep;
use Proximum\Vimeet\Application\Command\Register\ParticipantStepHandler;
use Proximum\Vimeet\Application\Event\Event\PreRegisterEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantUpdatedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetTitleCheckEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Event\User\RegisteredEvent;
use Proximum\Vimeet\Application\Event\User\RegistrationStepEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParticipantStepHandlerTest extends TestCase
{
    public function testHandle()
    {
        $date = new \DateTime();
        $event       = EventFactory::createEvent();
        $user        = UserFactory::create();
        $sheet       = SheetFactory::create($event, $user);
        $participant = ParticipantFactory::create($sheet, $user, $date);
        $locale      = 'fr';

        $sheet->setRegistrationData([]);
        $participant->setData([]);

        $data = [
            '69b3cde1' => ['text' => 'Vincent'],
            '69b3cde2' => ['text' => 'Dupond'],
            '69b3cde3' => ['text' => 'Proximum'],
            '69b3cde4' => ['text' => 'Paris'],
        ];

        $templateData = new TemplateData('root', [], 'fr', 'fr');

        $block = new Block('12', [], 'fr', 'fr');

        $editableText1     = new EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $editableText2     = new EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr');
        $editableTextSheet = new EditableText('69b3cde3', 'editable-text', [
            'tags' => ['sheet_title', 'sheet_data'],
        ], 'fr', 'fr');
        $editableTextSheet->setData(['text' => 'Proximum']);
        $editableTextCity = new EditableText('69b3cde4', 'editable-text', [
            'tags' => ['sheet_city', 'sheet_data'],
        ], 'fr', 'fr');

        $block->addChild(1, '69b3cde1', $editableText1);
        $block->addChild(1, '69b3cde2', $editableText2);
        $block->addChild(1, '69b3cde3', $editableTextSheet);
        $block->addChild(1, '69b3cde4', $editableTextCity);
        $templateData->addChild(0, '811f6edf', $block);

        $sheetRepository        = $this->prophesize(SheetRepositoryInterface::class);
        $participantRepository  = $this->prophesize(ParticipantRepositoryInterface::class);
        $accountSynchronizer    = $this->prophesize(Synchronizer::class);
        $eventDispatcher        = $this->prophesize(EventDispatcherInterface::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $userRepository         = $this->prophesize(UserRepositoryInterface::class);

        // Expected
        $expectedParticipant = ParticipantFactory::create($sheet, $user, $date);
        $expectedSheet       = clone $sheet;
        $expectedSheet->setTitle('Proximum');

        $expectedParticipant->setData([
            '69b3cde1' => ['text' => 'Vincent'],
            '69b3cde2' => ['text' => 'Dupond'],
        ]);
        $expectedSheet->setRegistrationData([
            '69b3cde3' => ['text' => 'Proximum'],
            '69b3cde4' => ['text' => 'Paris'],
        ]);

        $participantRepository->set($expectedParticipant)->shouldBeCalled();
        $sheetRepository->set($expectedSheet)->shouldBeCalled();
        $accountSynchronizer->set($templateData, $user)->shouldBeCalled();

        $userRepository->set($user)->shouldBeCalled();

        $eventDispatcher->dispatch(Events::USER_REGISTERED, Argument::type(RegisteredEvent::class))->shouldBeCalled();

        $eventDispatcher->dispatch(Events::EVENT_PRE_REGISTERED, Argument::type(PreRegisterEvent::class))
            ->shouldBeCalled();

        $eventDispatcher->dispatch(Events::REGISTRATION_STEP, new RegistrationStepEvent(
            $expectedSheet,
            $expectedParticipant,
            1
        ))->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($expectedSheet))->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_TITLE_CHECK, Argument::that(
            function (SheetTitleCheckEvent $sheetTitleCheckEvent) {
                return true;
            }
        ))->shouldBeCalled();

        $eventDispatcher
            ->dispatch(Events::PARTICIPANT_UPDATED, new ParticipantUpdatedEvent($expectedParticipant, $templateData))
            ->shouldBeCalled()
        ;

        $participantStep        = new ParticipantStep($templateData, $participant, 1, $locale, $data);
        $participantStepHandler = new ParticipantStepHandler(
            $sheetRepository->reveal(),
            $participantRepository->reveal(),
            $accountSynchronizer->reveal(),
            $eventDispatcher->reveal(),
            $participantInfoGuesser->reveal(),
            $userRepository->reveal()
        );

        $participantStepHandler->handle($participantStep);
    }
}
