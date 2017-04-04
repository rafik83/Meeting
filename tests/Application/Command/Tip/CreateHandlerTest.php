<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip;

use Doctrine\Common\Collections\ArrayCollection;
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
        $tip = new Tip('tipTitle', true, true, true, new \DateTime());
        $command->translations = [
            'locale_1' => [
                'locale' => 'locale_1',
                'content' => 'content_1',
                'title' => 'title_1',
            ],
        ];

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tip->translations = new ArrayCollection([
            'locale_1' => new TipTranslation($tip, new \DateTime(), 'title_1', 'locale_1', 'content_1')
        ]);

        $tipRepository->add($tip)->shouldBeCalled();

        $handler = new CreateHandler($tipRepository->reveal());
        $handler->handle($command);
    }
}