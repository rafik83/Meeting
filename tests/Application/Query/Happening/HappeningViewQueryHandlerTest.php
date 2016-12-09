<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Prophecy\Argument;
use Proximum\Vimeet\Application\View\Happening\HappeningCategoryView;
use Proximum\Vimeet\Application\View\Happening\HappeningSpeakerView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class HappeningViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event    = EventFactory::createEvent();
        $locale   = 'fr';
        $start    = new \DateTime();
        $end      = new \DateTime();
        $eventDay = new Day($event, $start, $end);
        $category = new Happening\Category($event, '', 1, '#aaa', '#bbb');

        $categoryTranslation = new Happening\CategoryTranslation($category, $locale, 'conference');
        $category->setTranslation($categoryTranslation);

        $happening1          = new Happening($event, $start, $end, $category);
        $speaker1            = new Happening\Speaker($event, 'john', 'doh', 'google', '', '');
        $speaker1Translation = new Happening\SpeakerTranslation($speaker1, $locale, 'developer');
        $speaker1->getTranslations()->set($locale, $speaker1Translation);
        $happening1->setSpeakers([$speaker1]);

        $happening2          = new Happening($event, $start, $end, $category);
        $happening2->setSpeakers([$speaker1]);

        $happenings = [$happening1, $happening2];

        // Mock
        $happeningRepository      = $this->prophesize(HappeningRepositoryInterface::class);
        $dayRepository            = $this->prophesize(DayRepositoryInterface::class);
        $speakerViewQueryHandler  = $this->prophesize(SpeakerViewQueryHandler::class);
        $categoryViewQueryHandler = $this->prophesize(CategoryViewQueryHandler::class);

        $dayRepository->findFirstDayByEvent($event)->shouldBeCalled()->willReturn($eventDay);
        $happeningRepository->findByEventAndDayAndCategory($event, $start, null)->shouldBeCalled()->willReturn($happenings);

        foreach ($happenings as $happening) {
            $categoryViewQueryHandler->handle(Argument::that(function (CategoryViewQuery $query) {
                return $query;
            }))->shouldBeCalled()->willReturn(new HappeningCategoryView(
                'conference', '', '#aaa', '#bbb'
            ));

            $speakerViewQueryHandler->handle(Argument::that(function (SpeakerViewQuery $query) {
                return $query;
            }))->shouldBeCalled()->willReturn([new HappeningSpeakerView(
                'john', 'doh', 'developer', '', ''
            )]);
        }

        $handler = new HappeningViewQueryHandler(
            $happeningRepository->reveal(),
            $dayRepository->reveal(),
            $speakerViewQueryHandler->reveal(),
            $categoryViewQueryHandler->reveal()
        );

        $handler->handle(new HappeningViewQuery($event, $locale, 1));
    }
}
