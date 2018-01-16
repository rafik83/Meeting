<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\AddHandler;
use Proximum\Vimeet\Application\Command\Participant\AddResult;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantAddedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetAddParticipantEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\UserEvent\TypeResolver;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AddHandlerTest extends TestCase
{
    public function testHandleWhenUserNotExists()
    {
        $now   = new \DateTime();
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $user  = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, $now);

        $planProduct        = Product::createPlan($event, 'plan', '', 100, 10, 40);
        $participantProduct = Product::createParticipant($event, 'participant', 50, 10);

        $package = new Package($event, 'My package', $now);
        $package->enable(true, true, true);
        $package->setPlans([$planProduct]);
        $package->setParticipants([$participantProduct]);
        $type->setPackage($package);

        $expectedSheet       = new Sheet($event, $type, [], $user, $now);
        $expectedUser        = new User('test@test.com', '', '', 'fr');
        $expectedParticipant = new Participant(
            $expectedSheet,
            $expectedUser,
            [
                '541f84d4' => [
                    'text' => 'jean',
                ],
                '838197c7' => [
                    'text' => 'truc',
                ],
            ],
            false
        );
        $expectedSheet->addParticipant($expectedParticipant);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test@test.com')->shouldBeCalled()->willReturn(null);
        $userRepository->add($expectedUser)->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add(
            Argument::that(
                function (Participant $participant) use ($expectedParticipant) {
                    return true;
                }
            )
        )->shouldBeCalled();

        $sheetRepository               = $this->prophesize(SheetRepositoryInterface::class);
        $templateDataFactory           = $this->prophesize(Template\TemplateDataFactory::class);
        $activateAccountTokenGenerator = $this->prophesize(ActivateAccountTokenGenerator::class);
        $eventDispatcher               = $this->prophesize(DelayedEventDispatcher::class);
        $typeResolver                  = $this->prophesize(TypeResolver::class);
        $accountSynchronizer           = $this->prophesize(Synchronizer::class);

        $expectedActivateAccountToken = new ActivateAccountToken(
            $expectedUser,
            'STRING',
            $sheet,
            $now
        );

        $activateAccountEvent = new ActivateAccountEvent(
            $expectedUser,
            $user,
            $event,
            $expectedActivateAccountToken,
            $sheet
        );

        $sheetAddConfirmationEvent = new SheetAddParticipantEvent(
            $expectedSheet,
            $expectedParticipant,
            $user
        );

        $activateAccountTokenGenerator->generate($expectedUser, $sheet)->shouldBeCalled()->willReturn(
            $expectedActivateAccountToken
        );

        $eventDispatcher->dispatch(
            Events::SHEET_ADD_PARTICIPANT_CONFIRMATION,
            $sheetAddConfirmationEvent
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            Events::PARTICIPANT_ADDED,
            new ParticipantAddedEvent($expectedParticipant)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(Events::USER_ACCOUNT_ACTIVATED, $activateAccountEvent)->shouldBeCalled();
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent)->shouldBeCalled();

        $cartManager = $this->prophesize(CartManager::class);

        $templateData  = new Template\TemplateData('root', [], 'fr', 'fr');
        $block         = new Template\Block('12', [], 'fr', 'fr');
        $editableText1 = new Template\TemplateObject\EditableText(
            '69b3cde1',
            'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr'
        );
        $editableText1->setContentValue('truc');
        $editableText2 = new Template\TemplateObject\EditableText(
            '69b3cde2',
            'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr'
        );
        $editableText2->setContentValue('bidule');

        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $templateData->addChild(0, '811f6edf', $block);
        $templateDataFactory->createRegistrationFromType($type, 'fr')->shouldBeCalled()->willReturn($templateData);

        $add            = new Add($sheet, 'fr', $user);
        $add->email     = 'test@test.com';
        $add->firstName = 'jean';
        $add->lastName  = 'truc';

        $handler = new AddHandler(
            $userRepository->reveal(),
            $participantRepository->reveal(),
            $sheetRepository->reveal(),
            $templateDataFactory->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal(),
            $cartManager->reveal(),
            $typeResolver->reveal(),
            $accountSynchronizer->reveal()
        );

        $this->assertEquals(new AddResult($expectedParticipant), $handler->handle($add));
    }

    public function testHandleWhenUserExists()
    {
        $now         = new \DateTime();
        $event       = EventFactory::createEvent();
        $type        = new Type($event);
        $user        = new User('test@test.com', '__SALT__', 'password', 'fr');
        $user2       = new User('test2@test.com', '__SALT__', 'password', 'fr');
        $sheet       = new Sheet($event, $type, [], $user, $now);
        $participant = new Participant(
            $sheet,
            $user2,
            [
                '541f84d4' => [
                    'text' => 'jean',
                ],
                '838197c7' => [
                    'text' => 'truc',
                ],
            ],
            false
        );
        $sheet->addParticipant($participant);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findByEmail('test2@test.com')->shouldBeCalled()->willReturn($user2);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $sheetRepository               = $this->prophesize(SheetRepositoryInterface::class);
        $templateDataFactory           = $this->prophesize(Template\TemplateDataFactory::class);
        $activateAccountTokenGenerator = $this->prophesize(ActivateAccountTokenGenerator::class);
        $eventDispatcher               = $this->prophesize(DelayedEventDispatcher::class);
        $typeResolver                  = $this->prophesize(TypeResolver::class);
        $accountSynchronizer           = $this->prophesize(Synchronizer::class);

        $cartManager = $this->prophesize(CartManager::class);

        $templateData  = new Template\TemplateData('root', [], 'fr', 'fr');
        $block         = new Template\Block('12', [], 'fr', 'fr');
        $editableText1 = new Template\TemplateObject\EditableText(
            '69b3cde1',
            'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr'
        );
        $editableText1->setContentValue('truc');
        $editableText2 = new Template\TemplateObject\EditableText(
            '69b3cde2',
            'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr'
        );
        $editableText2->setContentValue('bidule');

        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $templateData->addChild(0, '811f6edf', $block);

        $templateDataFactory->createRegistrationFromType($type, 'fr')->shouldNotBeCalled();

        $add            = new Add($sheet, 'fr', $user);
        $add->email     = 'test2@test.com';
        $add->firstName = 'jean';
        $add->lastName  = 'truc';

        $this->expectException(ParticipantAlreadyExistException::class);

        $handler = new AddHandler(
            $userRepository->reveal(),
            $participantRepository->reveal(),
            $sheetRepository->reveal(),
            $templateDataFactory->reveal(),
            $activateAccountTokenGenerator->reveal(),
            $eventDispatcher->reveal(),
            $cartManager->reveal(),
            $typeResolver->reveal(),
            $accountSynchronizer->reveal()
        );

        $handler->handle($add);
    }
}
