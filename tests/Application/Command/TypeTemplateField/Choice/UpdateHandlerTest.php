<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\TypeTemplateField\Choice;

use Proximum\Vimeet\Application\Command\TypeTemplateField\Choice\Update;
use Proximum\Vimeet\Application\Command\TypeTemplateField\Choice\UpdateHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\TemplateFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en']);

        $type = new Type($event);
        $type->setParticipantTemplate([
            'foobar' => [
                'label'          => ['fr' => 'foobar', 'en' => 'barfoo'],
                'type'           => 'lib_choice',
                'required'       => true,
                'private'        => false,
                'tags'           => [],
                'updatableUntil' => null,
                'placeholder'    => false,
                'position'       => 1,
                'choices'        => [
                    'item1' => ['label' => ['fr' => '1labelFr', 'en' => '1labelEn']],
                    'item2' => ['label' => ['fr' => '2labelFr', 'en' => '2labelEn']],
                ]
            ]
        ]);

        $expectedType = new Type($event);
        $expectedType->setParticipantTemplate([
            'foobar' => [
                'label'          => ['fr' => 'foobar', 'en' => 'barfoo'],
                'type'           => 'lib_choice',
                'required'       => true,
                'private'        => false,
                'tags'           => [],
                'updatableUntil' => null,
                'placeholder'    => ['fr' => 'Sélectionner', 'en' => 'Select'],
                'position'       => 1,
                'choices'        => [
                    'item1' => ['label' => ['fr' => '1labelFr', 'en' => '1labelEn']],
                    'item2' => ['label' => ['fr' => '2labelFr', 'en' => '2labelEn']],
                    'item3' => ['label' => ['fr' => '3labelFr', 'en' => '3labelEn']],
                ]
            ]
        ]);

        $templateFactory = new TemplateFactory();
        $template = $templateFactory->createTemplateFromArray($type->getTemplate('participant'));
        $group = $template->getGroup('default');
        $field = $group->getType('foobar');

        $update = new Update($type, 'participant', $field);

        $update->placeholder = ['fr' => 'Sélectionner', 'en' => 'Select'];
        $update->choices = [
            'item1' => ['label' => ['fr' => '1labelFr', 'en' => '1labelEn']],
            'item2' => ['label' => ['fr' => '2labelFr', 'en' => '2labelEn']],
            'item3' => ['label' => ['fr' => '3labelFr', 'en' => '3labelEn']],
        ];

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->set($expectedType)->shouldBeCalled();

        $handler = new UpdateHandler($typeRepository->reveal());
        $handler->handle($update);
    }
}
