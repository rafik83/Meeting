<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningException;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningExportViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningExportViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
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

        $happening = $this->prophesize(Happening::class);
        $happening->getId()->shouldBeCalled()->willReturn(1);
        $happening->getBegin()->shouldBeCalled()->willReturn($begin);
        $happening->getEnd()->shouldBeCalled()->willReturn($end);
        $happening->getCategory()->shouldBeCalled()->willReturn($category);
        $happening->getTitle($locale)->shouldBeCalled()->willReturn('SOLID master class');
        $happening->getDescription($locale)->shouldBeCalled()->willReturn('perfectionnez vous dans la qualité du code');

        $speaker = new Happening\Speaker($event, 'Martin', 'Simon', 'Amazon', '/logo.png', '/photo.png', null);
        $speakerTranslation = new Happening\SpeakerTranslation($speaker, $locale, 'Chef');
        $speaker->setTranslation($speakerTranslation);
        $happening->getSpeakers()->shouldBeCalled()->willReturn([$speaker]);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $eventUrlGenerator = $this->prophesize(EventUrlGenerator::class);
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);

        $happeningRepository->findListByEvent($event, $locale)->shouldBeCalled()->willReturn([$happening->reveal()]);
        $eventUrlGenerator->generateBaseEventAbsoluteUrl($event)->shouldBeCalled()->willReturn('http://test.com');
        $happeningParticipationRepository->getEvaluationsAverage($event)->shouldBeCalled()->willReturn([1 => 3]);
        $happeningParticipationRepository->getEvaluationsCount($event)->shouldBeCalled()->willReturn([1 => 25]);
        $scanRepository->getHappeningParticipantsCount($event)->shouldBeCalled()->willReturn([1 => 345]);

        $handler = new HappeningExportViewQueryHandler(
            $happeningRepository->reveal(),
            $eventUrlGenerator->reveal(),
            $scanRepository->reveal(),
            $happeningParticipationRepository->reveal()
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
        $this->assertEquals(345, $happeningExportView->getParticipantScanned());
        $this->assertEquals(25, $happeningExportView->getNumberOfGrades());
        $this->assertEquals(3, $happeningExportView->getAverageGrades());
    }

    public function testHandleNoHappening()
    {
        $this->expectException(EmptyHappeningException::class);

        $event  = EventFactory::createEvent();
        $locale = 'fr';

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $eventUrlGenerator = $this->prophesize(EventUrlGenerator::class);
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $happeningParticipationRepository->getEvaluationsCount($event)->shouldBeCalled()->willReturn([]);
        $happeningParticipationRepository->getEvaluationsAverage($event)->shouldBeCalled()->willReturn([]);
        $scanRepository->getHappeningParticipantsCount($event)->shouldBeCalled()->willReturn([]);
        $happeningRepository->findListByEvent($event, $locale)->shouldBeCalled()->willReturn([]);

        $handler = new HappeningExportViewQueryHandler(
            $happeningRepository->reveal(),
            $eventUrlGenerator->reveal(),
            $scanRepository->reveal(),
            $happeningParticipationRepository->reveal()
        );

        $this->expectException(EmptyHappeningException::class);

        $handler->handle(new HappeningExportViewQuery($event, $locale));
    }
}
