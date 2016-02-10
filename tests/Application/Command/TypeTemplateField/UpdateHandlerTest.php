<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\TypeTemplateField;

use Proximum\Vimeet\Application\Command\TypeTemplateField\UpdateLibChoice;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateChoiceHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en']);

        $type = new Type($event);
        $type->setParticipantTemplate([
            'foobar' => [
                'label'    => ['fr' => 'foobar', 'en' => 'barfoo'],
                'type'     => 'lib_choice',
                'required' => true,
                'private'  => false,
                'choices'  => [
                    ['label' => ['fr' => '1labelFr', 'en' => '1labelEn']],
                    ['label' => ['fr' => '2labelFr', 'en' => '2labelEn']],
                ]
            ]
        ]);

        $expectedType = new Type($event);
        $expectedType->setParticipantTemplate([
            'foobar' => [
                'label'    => ['fr' => 'foobar', 'en' => 'barfoo'],
                'type'     => 'lib_choice',
                'required' => true,
                'private'  => false,
                'choices'  => [
                    ['label' => ['fr' => '1labelFr', 'en' => '1labelEn']],
                    ['label' => ['fr' => '2labelFr', 'en' => '2labelEn']],
                    ['label' => ['fr' => '3labelFr', 'en' => '3labelEn']],
                ]
            ]
        ]);

        $update = new UpdateLibChoice($type, 'participantTemplate', 'foobar');
        $update->choices = [
            ['label' => ['fr' => '1labelFr', 'en' => '1labelEn']],
            ['label' => ['fr' => '2labelFr', 'en' => '2labelEn']],
            ['label' => ['fr' => '3labelFr', 'en' => '3labelEn']],
        ];

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->set($expectedType)->shouldBeCalled();

        $handler = new UpdateHandler($typeRepository->reveal());
        $handler->handle($update);
    }
}
