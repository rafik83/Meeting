<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Participant\UpdateProfile;
use Proximum\Vimeet\Application\Command\Participant\UpdateProfileHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetTitleCheckEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class UpdateProfileHandlerTest extends TestCase
{
    public function testHandle()
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $template = [
            '811f6edf' =>
                [
                    'component' => 'block',
                    'type'      => '12',
                    'config'    =>
                        [
                            'style' => 'style-1',
                        ],
                    'children'  =>
                        [
                            [
                                'dded0597' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'text',
                                        'config'    =>
                                            [
                                                'style'   => 'style-1',
                                                'content' =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Profil',
                                                    ],
                                                'type'    => 'titre',
                                            ],
                                    ],
                                '541f84d4' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Prénom',
                                                    ],
                                                'placeholder'  =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Prénom',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Merci de donner votre prénom',
                                                    ],
                                                'length'       => '250',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ["participant_firstname", "participant_data"],
                                            ],
                                    ],
                                '838197c7' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Nom',
                                                    ],
                                                'placeholder'  =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Nom',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => '',
                                                    ],
                                                'length'       => '250',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ["participant_lastname", "participant_data"],
                                            ],
                                    ],
                                '1efb9cbb' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Téléphone portable',
                                                    ],
                                                'placeholder'  =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Téléphone portable',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => '',
                                                    ],
                                                'length'       => '',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ["participant_mobile", "participant_data"],
                                            ],
                                    ],
                                '3b759fbb' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Téléphone fixe',
                                                    ],
                                                'placeholder'  =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Téléphone fixe',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => '',
                                                    ],
                                                'length'       => '25',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ["participant_phone", "participant_data"],
                                            ],
                                    ],
                            ],
                        ],
                ],
        ];

        $registrationTemplate = new RegistrationTemplate('Registration template', $template, ['fr'], 'fr', $now);
        $type->setRegistrationTemplate($registrationTemplate);

        $sheet       = new Sheet($event, $type, [], $user, $now);
        $participant = new Participant(
            $sheet,
            $user,
            [
                '541f84d4' => ['text' => 'truc'],
                '838197c7' => ['text' => 'muche'],
                '1efb9cbb' => ['telephone' => '+11111111'],
                '3b759fbb' => ['telephone' => '+22222222'],
            ],
            $owner = true,
            true
        );

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $accountSynchronizer   = $this->prophesize(Synchronizer::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);

        $sheetWithParticipant = new Sheet($event, $type, [], $user, $now);
        $expectedParticipant  = new Participant(
            $sheetWithParticipant,
            $user,
            [
                '541f84d4' => ['text' => 'foo'],
                '838197c7' => ['text' => 'bar'],
                '1efb9cbb' => ['telephone' => 'phone'],
                '3b759fbb' => ['telephone' => 'mobile'],
            ],
            $owner = true,
            true
        );

        $participantRepository->set($expectedParticipant)->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_UPDATED, Argument::that(
            function(SheetUpdatedEvent $sheetUpdatedEvent){
                return true;
            }
        ))->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_TITLE_CHECK, Argument::that(
            function(SheetTitleCheckEvent $sheetTitleCheckEvent){
                return true;
            }
        ))->shouldBeCalled();

        $handler = new UpdateProfileHandler(
            $participantRepository->reveal(),
            $accountSynchronizer->reveal(),
            $eventDispatcher->reveal()
        );

        $templateData  = new TemplateData('root', [], 'fr', 'fr');
        $block         = new Block('12', [], 'fr', 'fr');
        $text          = new TemplateObject\Text('69b3cde1', 'text', [], 'fr', 'fr');
        $editableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $editableText1->setContentValue('truc');
        $editableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr');
        $editableText2->setContentValue('bidule');
        $telephone1 = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone', 'participant_data'],
        ], 'fr', 'fr');
        $telephone1->setContentValue('+11111111');
        $telephone2 = new TemplateObject\Telephone('69b3cde2', 'telephone', [
            'tags' => ['participant_mobile', 'participant_data'],
        ], 'fr', 'fr');
        $telephone2->setContentValue('+22222222');

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $templateData->addChild(0, '811f6edf', $block);

        // Expected
        $expectedTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $expectedBlock        = new Block('12', [], 'fr', 'fr');
        $expectedText         = new TemplateObject\Text('69b3cde1', 'text', [], 'fr', 'fr');
        $exEditableText1      = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $exEditableText1->setContentValue('foo');
        $exEditableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr');
        $exEditableText2->setContentValue('bar');
        $exTelephone1 = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone', 'participant_data'],
        ], 'fr', 'fr');
        $exTelephone1->setContentValue('phone');
        $exTelephone2 = new TemplateObject\Telephone('69b3cde2', 'telephone', [
            'tags' => ['participant_mobile', 'participant_data'],
        ], 'fr', 'fr');
        $exTelephone2->setContentValue('mobile');

        $expectedBlock->addChild(1, 'dded0597', $expectedText);
        $expectedBlock->addChild(1, '541f84d4', $exEditableText1);
        $expectedBlock->addChild(1, '838197c7', $exEditableText2);
        $expectedBlock->addChild(1, '1efb9cbb', $exTelephone1);
        $expectedBlock->addChild(1, '3b759fbb', $exTelephone2);
        $expectedTemplateData->addChild(0, '811f6edf', $expectedBlock);

        $accountSynchronizer->set($expectedTemplateData, $user)->shouldBeCalled();

        $handler->handle(
            new UpdateProfile(
                $templateData,
                $participant,
                'fr',
                [
                    '541f84d4' => ['text' => 'foo'],
                    '838197c7' => ['text' => 'bar'],
                    '1efb9cbb' => ['telephone' => 'phone'],
                    '3b759fbb' => ['telephone' => 'mobile'],
                ],
                $user
            )
        );
    }

    public function testWithoutSynchronizer()
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2 = new User('test2@test.com', 'salt2', 'password2', 'fr');
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $template = [
            '811f6edf' =>
                [
                    'component' => 'block',
                    'type'      => '12',
                    'config'    =>
                        [
                            'style' => 'style-1',
                        ],
                    'children'  =>
                        [
                            [
                                'dded0597' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'text',
                                        'config'    =>
                                            [
                                                'style'   => 'style-1',
                                                'content' =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Profil',
                                                    ],
                                                'type'    => 'titre',
                                            ],
                                    ],
                                '541f84d4' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Prénom',
                                                    ],
                                                'placeholder'  =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Prénom',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Merci de donner votre prénom',
                                                    ],
                                                'length'       => '250',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ["participant_firstname", "participant_data"],
                                            ],
                                    ],
                                '838197c7' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Nom',
                                                    ],
                                                'placeholder'  =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Nom',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => '',
                                                    ],
                                                'length'       => '250',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ["participant_lastname", "participant_data"],
                                            ],
                                    ],
                                '1efb9cbb' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Téléphone portable',
                                                    ],
                                                'placeholder'  =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Téléphone portable',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => '',
                                                    ],
                                                'length'       => '',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ["participant_mobile", "participant_data"],
                                            ],
                                    ],
                                '3b759fbb' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Téléphone fixe',
                                                    ],
                                                'placeholder'  =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Téléphone fixe',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => '',
                                                    ],
                                                'length'       => '25',
                                                'required'     => true,
                                                'type'         => 'text',
                                                'translatable' => false,
                                                'tags'         => ["participant_phone", "participant_data"],
                                            ],
                                    ],
                            ],
                        ],
                ],
        ];

        $registrationTemplate = new RegistrationTemplate('Registration template', $template, ['fr'], 'fr', $now);
        $type->setRegistrationTemplate($registrationTemplate);

        $sheet       = new Sheet($event, $type, [], $user, $now);
        $participant = new Participant(
            $sheet,
            $user,
            [
                '541f84d4' => ['text' => 'truc'],
                '838197c7' => ['text' => 'muche'],
                '1efb9cbb' => ['telephone' => '+11111111'],
                '3b759fbb' => ['telephone' => '+22222222'],
            ],
            $owner = true,
            true
        );

        // Mock
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $accountSynchronizer   = $this->prophesize(Synchronizer::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcher::class);

        $sheetWithParticipant = new Sheet($event, $type, [], $user, $now);
        $expectedParticipant  = new Participant(
            $sheetWithParticipant,
            $user,
            [
                '541f84d4' => ['text' => 'foo'],
                '838197c7' => ['text' => 'bar'],
                '1efb9cbb' => ['telephone' => 'phone'],
                '3b759fbb' => ['telephone' => 'mobile'],
            ],
            $owner = true,
            true
        );

        $participantRepository->set($expectedParticipant)->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_UPDATED, Argument::that(
            function(SheetUpdatedEvent $sheetUpdatedEvent){
                return true;
            }
        ))->shouldBeCalled();

        $eventDispatcher->dispatch(Events::SHEET_TITLE_CHECK, Argument::that(
            function(SheetTitleCheckEvent $sheetTitleCheckEvent){
                return true;
            }
        ))->shouldBeCalled();

        $handler = new UpdateProfileHandler(
            $participantRepository->reveal(),
            $accountSynchronizer->reveal(),
            $eventDispatcher->reveal()
        );

        $templateData  = new TemplateData('root', [], 'fr', 'fr');
        $block         = new Block('12', [], 'fr', 'fr');
        $text          = new TemplateObject\Text('69b3cde1', 'text', [], 'fr', 'fr');
        $editableText1 = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $editableText1->setContentValue('truc');
        $editableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr');
        $editableText2->setContentValue('bidule');
        $telephone1 = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone', 'participant_data'],
        ], 'fr', 'fr');
        $telephone1->setContentValue('+11111111');
        $telephone2 = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_mobile', 'participant_data'],
        ], 'fr', 'fr');
        $telephone2->setContentValue('+22222222');

        $block->addChild(1, 'dded0597', $text);
        $block->addChild(1, '541f84d4', $editableText1);
        $block->addChild(1, '838197c7', $editableText2);
        $block->addChild(1, '1efb9cbb', $telephone1);
        $block->addChild(1, '3b759fbb', $telephone2);
        $templateData->addChild(0, '811f6edf', $block);

        // Expected
        $expectedTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $expectedBlock        = new Block('12', [], 'fr', 'fr');
        $expectedText         = new TemplateObject\Text('69b3cde1', 'text', [], 'fr', 'fr');
        $exEditableText1      = new TemplateObject\EditableText('69b3cde1', 'editable-text', [
            'tags' => ['participant_firstname', 'participant_data'],
        ], 'fr', 'fr');
        $exEditableText1->setContentValue('foo');
        $exEditableText2 = new TemplateObject\EditableText('69b3cde2', 'editable-text', [
            'tags' => ['participant_lastname', 'participant_data'],
        ], 'fr', 'fr');
        $exEditableText2->setContentValue('bar');
        $exTelephone1 = new TemplateObject\Telephone('69b3cde1', 'telephone', [
            'tags' => ['participant_phone', 'participant_data'],
        ], 'fr', 'fr');
        $exTelephone1->setContentValue('phone');
        $exTelephone2 = new TemplateObject\Telephone('69b3cde2', 'telephone', [
            'tags' => ['participant_mobile', 'participant_data'],
        ], 'fr', 'fr');
        $exTelephone2->setContentValue('mobile');

        $expectedBlock->addChild(1, 'dded0597', $expectedText);
        $expectedBlock->addChild(1, '541f84d4', $exEditableText1);
        $expectedBlock->addChild(1, '838197c7', $exEditableText2);
        $expectedBlock->addChild(1, '1efb9cbb', $exTelephone1);
        $expectedBlock->addChild(1, '3b759fbb', $exTelephone2);
        $expectedTemplateData->addChild(0, '811f6edf', $expectedBlock);

        $accountSynchronizer->set($expectedTemplateData, $user)->shouldNotBeCalled();

        $handler->handle(
            new UpdateProfile(
                $templateData,
                $participant,
                'fr',
                [
                    '541f84d4' => ['text' => 'foo'],
                    '838197c7' => ['text' => 'bar'],
                    '1efb9cbb' => ['telephone' => 'phone'],
                    '3b759fbb' => ['telephone' => 'mobile'],
                ],
                $user2
            )
        );
    }
}
