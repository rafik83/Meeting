<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip;

use Proximum\Vimeet\Application\Command\Tip\Create;
use Proximum\Vimeet\Application\Command\Tip\CreateHandler;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $command = new Create();
        $command->onCatalog = false;
        $command->onMeetingManagement = true;
        $command->onPrintPlanning = true;
        $command->title = 'tipTitle';
        $command->translations = [
            'locale_1' => [
                'title'   => 'title_1',
                'locale'  => 'locale_1',
                'content' => 'content_1',
            ],
            'locale_2' => [
                'title'   => 'title_2',
                'locale'  => 'locale_2',
                'content' => 'content_2',
            ],
        ];

        $tip = new Tip('tipTitle', true, false, true);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $tip->setTranslation($command->translations['locale_1']);
        $tip->setTranslation($command->translations['locale_2']);

        $tipRepository->add($tip)->shouldBeCalled();

        $handler = new CreateHandler($tipRepository->reveal());
        $handler->handle($command);
    }
}