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
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $command = new Create(['fr' ,'en']);
        $command->onCatalog = true;
        $command->onMeetingManagement = true;
        $command->onPrintPlanning = true;
        $command->title = 'tipTitle';
        $tip = new Tip('tipTitle', true, true, true);
        $command->translations = new TipTranslation(
            $tip,
            'title_1',
            'locale_1',
            'content_1'
        );

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        foreach ($command->translations as $translation) {
            $tip->setTranslation($translation);
        }

        $tipRepository->set($tip)->shouldBeCalled();

        $handler = new CreateHandler($tipRepository->reveal());
        $handler->handle($command);
    }
}