<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\User;

use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Command\User\ParticipateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataValidator;

class ParticipateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $now   = new \DateTime();
        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $event = new Event();
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
                                            ],
                                    ],
                                'dd321a4f' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'nomenclature',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Service (Juridique, Informatique, ...)',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => '',
                                                    ],
                                                'nomenclature' => '1',
                                            ],
                                    ],
                                '6c4a3a4f' =>
                                    [
                                        'component' => 'object',
                                        'type'      => 'nomenclature',
                                        'config'    =>
                                            [
                                                'style'        => 'style-1',
                                                'label'        =>
                                                    [
                                                        'en' => null,
                                                        'fr' => 'Fonction',
                                                    ],
                                                'help'         =>
                                                    [
                                                        'en' => null,
                                                        'fr' => '',
                                                    ],
                                                'nomenclature' => '4',
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
                                            ],
                                    ],
                            ],
                        ],
                ],
            '4bb4db28' => [],
            '67019e4a' => [],
        ];

        $registrationTemplate = new RegistrationTemplate('Registration template', $template, ['fr'], 'fr', $now);
        $type->setRegistrationTemplate($registrationTemplate);

        $sheetRepository       = $this->prophesize(SheetRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $expectedSheet = new Sheet($event, $type, [], [], $now);
        $sheetRepository->add($expectedSheet)->shouldBeCalled();

        $expectedSheetWithParticipant = new Sheet($event, $type, [], [], $now);
        $expectedParticipant          = new Participant(
            $expectedSheetWithParticipant,
            $user,
            [
                '541f84d4' => ['text' => 'foo'],
                '838197c7' => ['text' => 'bar'],
                '1efb9cbb' => ['text' => 'mobile'],
                '3b759fbb' => ['text' => 'phone'],
            ],
            $owner = true,
            true
        );

        $participantRepository->add($expectedParticipant)->shouldBeCalled();

        $templateDataValidator = $this->prophesize(TemplateDataValidator::class);

        $handler = new ParticipateHandler(
            $sheetRepository->reveal(),
            $participantRepository->reveal(),
            $templateDataValidator->reveal(),
            $now
        );

        $handler->handle(
            new Participate(
                $user,
                $event,
                $type,
                'fr',
                [
                    '541f84d4' => ['text' => 'foo'],
                    '838197c7' => ['text' => 'bar'],
                    '1efb9cbb' => ['text' => 'mobile'],
                    '3b759fbb' => ['text' => 'phone'],
                ]
            )
        );
    }
}
