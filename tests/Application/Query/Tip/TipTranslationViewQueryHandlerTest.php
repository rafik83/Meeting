<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Tip;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Tip\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipTranslationViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();

        $tip = new Tip('tip', true, true, true, $dateTime);
        $tip->translations = [
            'fr' => new TipTranslation($tip, $dateTime, 'title', 'fr', 'content'),
        ];

        $tip2 = new Tip('tip2', true, true, true, $dateTime);
        $tip2->translations = [
            'fr' => new TipTranslation($tip2, $dateTime, 'title2', 'fr', 'content2'),
        ];
        $tipView1 = new TipTranslationView(1, 'title', 'content');
        $tipView2 = new TipTranslationView(2, 'title1', 'content2');

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $query = new TipTranslationViewQuery('event_catalog_index', 'fr');
        $expectedViews = [$tipView1, $tipView2];

        $handler = new TipTranslationViewQueryHandler($tipRepository->reveal());

        $tipRepository->getByContext('onCatalog', $query->locale)->shouldBeCalled()->willReturn($expectedViews);

        $views = $handler->handle($query);
        $this->assertEquals($expectedViews, $views);

    }
}
