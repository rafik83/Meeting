<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Tip;


use Doctrine\Common\Collections\ArrayCollection;
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
        $tip = new Tip('tip', false, true, false, $dateTime);
        $tip->translations = new ArrayCollection([
            'fr' => new TipTranslation($tip, $dateTime, 'title', 'fr', 'content'),
        ]);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $query = new TipTranslationViewQuery('event_catalog_index', 'fr');
        $expectedViews = new TipTranslationView('title', 'content');

        $handler = new TipTranslationViewQueryHandler($tipRepository->reveal());

        $tipRepository->findForCatalog($query->path)->shouldBeCalled()->willReturn($tip);

        $view = $handler->handle($query);
        $this->assertEquals($expectedViews, $view);

    }
}
