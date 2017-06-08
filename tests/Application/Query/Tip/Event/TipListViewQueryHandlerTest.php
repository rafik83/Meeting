<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Tip\Event;

use Proximum\Vimeet\Application\Query\Tip\Event\TipListViewQuery;
use Proximum\Vimeet\Application\Query\Tip\Event\TipListViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\Event\TipView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\TipFactory;

class TipListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $tip = TipFactory::createTip('title');

        $expectedView = [new TipView(null, 'title', $locale)];

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $query  = new TipListViewQuery($locale);
        $handler = new TipListViewQueryHandler($tipRepository->reveal());

        $tipRepository->getAll()
            ->shouldBeCalled()
            ->willReturn([$tip]);

        $resultView = $handler->handle($query);

        $this->assertEquals($expectedView, $resultView);
    }
}
