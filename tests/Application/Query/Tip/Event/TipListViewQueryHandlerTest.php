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
use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TipListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $locale = 'fr';
        $tipList = [
            new TipTranslationView(1, 'title_fr', 'content_fr', 'admin_title'),
        ];

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $query  = new TipListViewQuery($event, $locale);
        $handler = new TipListViewQueryHandler($tipRepository->reveal());

        $tipRepository->findAll($query->locale)
            ->shouldBeCalled()
            ->willReturn($tipList);

        $handler->handle($query);
    }
}
