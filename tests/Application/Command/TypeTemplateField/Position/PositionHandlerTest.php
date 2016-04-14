<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\TypeTemplateField\Text;

use Proximum\Vimeet\Application\Command\TypeTemplateField\Position\Position;
use Proximum\Vimeet\Application\Command\TypeTemplateField\Position\PositionHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\TemplateFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class PositionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setLocales(['fr', 'en'], 'fr');

        $type = new Type($event);
        $type->setParticipantTemplate([
            'foobar' => [
                'label'               => ['fr' => 'foobar', 'en' => 'foobar'],
                'type'                => 'lib_text',
                'required'            => true,
                'private'             => false,
                'tags'                => [],
                'updatableUntil'      => null,
                'translationRequired' => false,
                'translatable'        => false,
                'position'            => 1,
            ],
            'barfoo' => [
                'label'               => ['fr' => 'barfoo', 'en' => 'barfoo'],
                'type'                => 'lib_text',
                'required'            => true,
                'private'             => false,
                'tags'                => [],
                'updatableUntil'      => null,
                'translationRequired' => false,
                'translatable'        => false,
                'position'            => 2,
            ],
        ]);

        $expectedType = new Type($event);
        $expectedType->setParticipantTemplate([
            'foobar' => [
                'label'               => ['fr' => 'foobar', 'en' => 'foobar'],
                'type'                => 'lib_text',
                'required'            => true,
                'private'             => false,
                'tags'                => [],
                'updatableUntil'      => null,
                'translationRequired' => false,
                'translatable'        => false,
                'position'            => 2,
            ],
            'barfoo' => [
                'label'               => ['fr' => 'barfoo', 'en' => 'barfoo'],
                'type'                => 'lib_text',
                'required'            => true,
                'private'             => false,
                'tags'                => [],
                'updatableUntil'      => null,
                'translationRequired' => false,
                'translatable'        => false,
                'position'            => 1,
            ],
        ]);

        $templateFactory = new TemplateFactory();
        $template = $templateFactory->createTemplateFromArray($type->getTemplate('participant'));
        $group = $template->getGroup('default');

        $fieldsOrder = [
            'foobar' => 2,
            'barfoo' => 1,
        ];

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->set($expectedType)->shouldBeCalled();

        $order   = new Position($type, 'participant', $group, $fieldsOrder);
        $handler = new PositionHandler($typeRepository->reveal());
        $handler->handle($order);
    }
}
