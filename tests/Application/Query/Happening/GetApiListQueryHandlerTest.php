<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Happening\GetApiListQuery;
use Proximum\Vimeet\Application\Query\Happening\GetApiListQueryHandler;
use Proximum\Vimeet\Application\View\Happening\ApiSpeakerView;
use Proximum\Vimeet\Application\View\Happening\ApiView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class GetApiListQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $locale = 'fr';

        $category = $this->prophesize(Category::class);
        $category->getTitle($locale)->willReturn('category #1');

        $happening1 = $this->prophesize(Happening::class);
        $happening1->getId()->willReturn(123);
        $happening1->getTitle($locale)->willReturn('Conference #1');
        $happening1->getDescription($locale)->willReturn('Lorem ipsum 1');
        $happening1->getBegin()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 15:00'));
        $happening1->getEnd()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 16:00'));
        $happening1->getSpeakers()->willReturn([]);
        $happening1->isInteractiveWebinar()->willReturn(false);
        $happening1->isVideoWebinar()->willReturn(false);
        $happening1->isWebinar()->willReturn(false);
        $happening1->getCategory()->willReturn($category->reveal());
        $type1 = $this->prophesize(Type::class);
        $type1->getId()->willreturn(1);
        $type2 = $this->prophesize(Type::class);
        $type2->getId()->willreturn(2);
        $happening1->getTypes()->willReturn([$type1->reveal(), $type2->reveal()]);

        $happening2 = $this->prophesize(Happening::class);
        $happening2->getId()->willReturn(456);
        $happening2->getTitle($locale)->willReturn('Conference #2');
        $happening2->getDescription($locale)->willReturn('Lorem ipsum 2');
        $happening2->getBegin()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 17:00'));
        $happening2->getEnd()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 18:00'));
        $speaker = $this->prophesize(Speaker::class);
        $speaker->getFirstname()->willReturn('John');
        $speaker->getLastname()->willReturn('Doe');
        $speaker->getPosition($locale)->willReturn('PDG Acme Corp.');
        $speaker->getPhoto()->willReturn('/assets/boss-picture.jpg');
        $speaker->getLogo()->willReturn('/assets/acme-corp-logo.png');
        $happening2->getSpeakers()->willReturn([$speaker->reveal()]);
        $happening2->isInteractiveWebinar()->willReturn(false);
        $happening2->isVideoWebinar()->willReturn(false);
        $happening2->isWebinar()->willReturn(true);
        $happening2->getCategory()->willReturn($category->reveal());
        $happening2->getTypes()->willReturn([$type2->reveal()]);

        $happening3 = $this->prophesize(Happening::class);
        $happening3->getId()->willReturn(789);
        $happening3->getTitle($locale)->willReturn('Conference #3');
        $happening3->getDescription($locale)->willReturn('Lorem ipsum 3');
        $happening3->getBegin()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 17:00'));
        $happening3->getEnd()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 18:00'));
        $happening3->getSpeakers()->willReturn([]);
        $happening3->isInteractiveWebinar()->willReturn(false);
        $happening3->isVideoWebinar()->willReturn(true);
        $happening3->isWebinar()->willReturn(true);
        $happening3->getCategory()->willReturn($category->reveal());
        $happening3->getTypes()->willReturn([$type1->reveal()]);

        $happening4 = $this->prophesize(Happening::class);
        $happening4->getId()->willReturn(101);
        $happening4->getTitle($locale)->willReturn('Conference #4');
        $happening4->getDescription($locale)->willReturn('Lorem ipsum 4');
        $happening4->getBegin()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 17:00'));
        $happening4->getEnd()->willReturn(\DateTime::createFromFormat('!Y-m-d H:i', '2020-03-14 18:00'));
        $happening4->getSpeakers()->willReturn([]);
        $happening4->isInteractiveWebinar()->willReturn(true);
        $happening4->isVideoWebinar()->willReturn(false);
        $happening4->isWebinar()->willReturn(true);
        $happening4->getCategory()->willReturn($category->reveal());
        $happening4->getTypes()->willReturn([$type2->reveal()]);

        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->findByEventAndTypes($event->reveal(), null)
            ->shouldBeCalled()
            ->willReturn([$happening1->reveal(), $happening2->reveal(), $happening3->reveal(), $happening4->reveal()]);

        // Expected
        $expectedViews = [
            new ApiView(
                123,
                'Conference #1',
                'Lorem ipsum 1',
                '2020-03-14T15:00:00+0000',
                '2020-03-14T16:00:00+0000',
                'default',
                'category #1',
                [],
                [1,2]
            ),
            new ApiView(
                456,
                'Conference #2',
                'Lorem ipsum 2',
                '2020-03-14T17:00:00+0000',
                '2020-03-14T18:00:00+0000',
                'webinar',
                'category #1',
                [
                    new ApiSpeakerView(
                        'John',
                        'Doe',
                        'PDG Acme Corp.',
                        'https://static.example.net/assets/boss-picture.jpg',
                        'https://static.example.net/assets/acme-corp-logo.png'
                    )
                ],
                [2]
            ),
            new ApiView(
                789,
                'Conference #3',
                'Lorem ipsum 3',
                '2020-03-14T17:00:00+0000',
                '2020-03-14T18:00:00+0000',
                'webinar_video',
                'category #1',
                [],
                [1]
            ),
            new ApiView(
                101,
                'Conference #4',
                'Lorem ipsum 4',
                '2020-03-14T17:00:00+0000',
                '2020-03-14T18:00:00+0000',
                'webinar_interactive',
                'category #1',
                [],
                [2]
            ),
        ];

        $handler = new GetApiListQueryHandler($happeningRepository->reveal());
        $views = $handler->handle(new GetApiListQuery($event->reveal(), $locale, 'https://static.example.net', null));

        $this->assertEquals($expectedViews, $views);
    }
}
