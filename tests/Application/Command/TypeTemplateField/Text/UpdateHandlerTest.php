<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\TypeTemplateField\Text;

use Proximum\Vimeet\Application\Command\TypeTemplateField\Text\Update;
use Proximum\Vimeet\Application\Command\TypeTemplateField\Text\UpdateHandler;
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
                'label'               => ['fr' => 'barfoo_fr', 'en' => 'barfoo_en'],
                'type'                => 'lib_text',
                'required'            => true,
                'private'             => false,
                'tags'                => [],
                'updatableUntil'      => null,
                'translationRequired' => false,
                'translatable'        => false,
                'position'            => 1,
            ]
        ]);

        $expectedType = new Type($event);
        $expectedType->setParticipantTemplate([
            'foobar' => [
                'label'               => ['fr' => 'foobar_fr', 'en' => 'foobar_en'],
                'type'                => 'lib_text',
                'required'            => false,
                'private'             => true,
                'tags'                => [],
                'updatableUntil'      => null,
                'translationRequired' => true,
                'translatable'        => true,
                'position'            => 1,
            ]
        ]);

        $templateFactory = new TemplateFactory();
        $template = $templateFactory->createTemplateFromArray($type->getTemplate('participant'));
        $group = $template->getGroup('default');
        $field = $group->getType('foobar');

        $update = new Update($type, 'participant', $field);

        $update->label = ['fr' => 'foobar_fr', 'en' => 'foobar_en'];
        $update->required = false;
        $update->private = true;
        $update->translatable = true;
        $update->translationRequired = true;

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->set($expectedType)->shouldBeCalled();

        $handler = new UpdateHandler($typeRepository->reveal());
        $handler->handle($update);
    }
}
