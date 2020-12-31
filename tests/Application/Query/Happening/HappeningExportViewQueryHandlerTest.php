<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningException;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningExportViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningExportViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\EventUrlGenerator;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class HappeningExportViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';

        $begin     = new \DateTime('2018-02-03 18:23:24');
        $end       = new \DateTime('2018-02-03 18:59:10');

        $category  = new Happening\Category($event, 'picto', 1, '#000', '#fff');
        $categoryTranslation = new Happening\CategoryTranslation($category, $locale,'4developers');
        $category->setTranslation($categoryTranslation);

        $happening = new Happening($event, $begin, $end, $category, []);

        $speaker = new Happening\Speaker($event, 'Martin', 'Simon', 'Amazon', '/logo.png', '/photo.png', null);
        $speakerTranslation = new Happening\SpeakerTranslation($speaker, $locale, 'Chef');
        $speaker->setTranslation($speakerTranslation);

        $happeningTranslation = new Happening\HappeningTranslation($happening, $locale, 'SOLID master class', 'perfectionnez vous dans la qualité du code');

        $happening->setTranslation($happeningTranslation);
        $happening->setSpeakers([$speaker]);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $eventUrlGenerator = $this->prophesize(EventUrlGenerator::class);

        $happeningRepository->findListByEvent($event, $locale)->shouldBeCalled()->willReturn([$happening]);
        $eventUrlGenerator->generateBaseEventAbsoluteUrl($event)->shouldBeCalled()->willReturn('http://test.com');

        $handler = new HappeningExportViewQueryHandler(
            $happeningRepository->reveal(),
            $eventUrlGenerator->reveal()
        );

        $happeningListView = $handler->handle(
            new HappeningExportViewQuery($event, $locale)
        );

        $this->assertCount(1, $happeningListView->getHappeningExportListView());
        $happeningExportView = $happeningListView->getHappeningExportListView()[0];

        $this->assertCount(1, $happeningExportView->speakersListView->getSpeakersListView());
        $speakersView = $happeningExportView->speakersListView->getSpeakersListView()[0];

        $this->assertEquals('03-02-2018 18:23', $happeningExportView->getBegin());
        $this->assertEquals('SOLID master class', $happeningExportView->getTitle());
        $this->assertEquals('4developers', $happeningExportView->getCategory());
        $this->assertEquals('perfectionnez vous dans la qualité du code', $happeningExportView->getDescription());
        $this->assertEquals('03-02-2018 18:59', $happeningExportView->getEnd());
        $this->assertEquals('03-02-2018 18:59', $happeningExportView->getEnd());
        $this->assertEquals('Martin SIMON', $speakersView->getName());
        $this->assertEquals('Amazon', $speakersView->getSociety());
        $this->assertEquals('Chef', $speakersView->getPosition());
        $this->assertEquals('http://test.com/logo.png', $speakersView->getUrlLogo());
        $this->assertEquals('http://test.com/photo.png', $speakersView->getUrlAvatar());
    }

    public function testHandleNoHappening()
    {
        $this->expectException(EmptyHappeningException::class);

        $event  = EventFactory::createEvent();
        $locale = 'fr';

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $eventUrlGenerator = $this->prophesize(EventUrlGenerator::class);

        $happeningRepository->findListByEvent($event, $locale)->shouldBeCalled()->willReturn([]);

        $handler = new HappeningExportViewQueryHandler(
            $happeningRepository->reveal(),
            $eventUrlGenerator->reveal()
        );

        $this->expectException(EmptyHappeningException::class);

        $handler->handle(new HappeningExportViewQuery($event, $locale));
    }
}
