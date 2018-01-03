<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\HappeningSpeakerView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class SpeakerViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $begin     = new \DateTime();
        $end       = new \DateTime();
        $category  = new Happening\Category($event, '', 1, '#aaa', '#bbb');
        $happening = new Happening($event, $begin, $end, $category, []);

        $speaker1            = new Happening\Speaker($event, 'john', 'doh', 'google', '', '');
        $speaker1Translation = new Happening\SpeakerTranslation($speaker1, $locale, 'developer');
        $speaker1->getTranslations()->set($locale, $speaker1Translation);

        $speaker2            = new Happening\Speaker($event, 'foo', 'bar', 'microsoft', '', '');
        $speaker2Translation = new Happening\SpeakerTranslation($speaker2, $locale, 'ceo');
        $speaker2->getTranslations()->set($locale, $speaker2Translation);

        $speakers = [$speaker1, $speaker2];

        $happening->setSpeakers($speakers);

        // Expected
        $expectedSpeakerViews = [
            new HappeningSpeakerView('john', 'doh', 'developer', '', ''),
            new HappeningSpeakerView('foo', 'bar', 'ceo', '', ''),
        ];

        $handler = new SpeakerViewQueryHandler();
        $views   = $handler->handle(new SpeakerViewQuery($happening, $locale));

        $this->assertEquals($expectedSpeakerViews, $views);
        $this->assertEquals($views[0]->hasPicture(), false);
        $this->assertEquals($views[1]->hasPicture(), false);
    }
}
