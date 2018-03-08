<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Tip;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class TipTranslationViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $sheet    = SheetFactory::create($event);

        $tip = new Tip('tip', null, true, true, true, false, false, false, false, $dateTime);
        $tip->setTranslation('fr', 'title', 'content', $dateTime);

        $tip2 = new Tip('tip2', null, true, true, true, false, false, false, false, $dateTime);
        $tip2->setTranslation('fr', 'title2', 'content2', $dateTime);

        $tipView1 = new TipTranslationView(1, 'title', 'content');
        $tipView2 = new TipTranslationView(2, 'title1', 'content2');

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $query = new TipTranslationViewQuery($sheet->getType(), 'event_catalog_index', 'fr');
        $expectedViews = [$tipView1, $tipView2];

        $handler = new TipTranslationViewQueryHandler($tipRepository->reveal());

        $tipRepository->getByContextAndEventAndType(
            $query->event,
            $query->type,
            'onCatalog',
            $query->locale
        )->shouldBeCalled()->willReturn($expectedViews);

        $views = $handler->handle($query);
        $this->assertEquals($expectedViews, $views);
    }
}
