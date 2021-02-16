<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\SpeakerViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\SpeakerViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\SpeakerView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Tests\Factory\EventFactory;

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

        $speaker1            = new Happening\Speaker($event, 'john', 'doh', 'google', '', '', null);
        $speaker1Translation = new Happening\SpeakerTranslation($speaker1, $locale, 'developer');
        $speaker1->getTranslations()->set($locale, $speaker1Translation);

        $speaker2            = new Happening\Speaker($event, 'foo', 'bar', 'microsoft', '', '', null);
        $speaker2Translation = new Happening\SpeakerTranslation($speaker2, $locale, 'ceo');
        $speaker2->getTranslations()->set($locale, $speaker2Translation);

        $speakers = [$speaker1, $speaker2];

        $happening->setSpeakers($speakers);

        // Expected
        $expectedSpeakerViews = [
            new SpeakerView('john', 'doh', 'developer', '', ''),
            new SpeakerView('foo', 'bar', 'ceo', '', ''),
        ];

        $handler = new SpeakerViewQueryHandler();
        $views   = $handler->handle(new SpeakerViewQuery($happening, $locale));

        $this->assertEquals($expectedSpeakerViews, $views);
    }
}
