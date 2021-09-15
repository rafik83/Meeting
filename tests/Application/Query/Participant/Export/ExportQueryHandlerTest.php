<?php

namespace Proximum\Vimeet\Tests\Application\Query\Participant\Export;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Participant\Export\ExportQuery;
use Proximum\Vimeet\Application\Query\Participant\Export\ExportQueryHandler;
use Proximum\Vimeet\Application\Query\Participant\Export\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Participant\Export\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantListView;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class ExportQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $participantRepository;

    /** @var ObjectProphecy */
    private $participantViewQueryHandler;

    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $productRepository;

    /** @var ObjectProphecy */
    private $participant1;

    /** @var ObjectProphecy */
    private $participant2;

    /** @var ObjectProphecy */
    private $participant3;

    /** @var ObjectProphecy */
    private $sheet1;

    /** @var ObjectProphecy */
    private $sheet2;

    /** @var ObjectProphecy */
    private $sheet3;

    /** @var ObjectProphecy */
    private $type1;

    /** @var ObjectProphecy */
    private $type2;

    /** @var ObjectProphecy */
    private $product1;

    /** @var ObjectProphecy */
    private $product2;

    /** @var ObjectProphecy */
    private $product3;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $day;

    /** @var ObjectProphecy */
    private $happeningRepository;

    /** @var ObjectProphecy */
    private $requestRepository;

    /** @var ObjectProphecy */
    private $meetingRepository;

    public function setUp()
    {
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->participantViewQueryHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $this->happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $this->event = $this->prophesize(Event::class);
        $this->event->getLocaleFallback()->willReturn('en');

        $this->day = $this->prophesize(Event\Day::class);
        $this->event->getDays()->willReturn([$this->day->reveal()]);
        $this->event->getAvailableLocale('fr')->willReturn('fr');

        $this->day->getId()->willReturn(123);
        $dateTime = new \DateTime('2018-10-10 10:00:00.000');
        $this->day->getBegin()->willReturn($dateTime);

        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant3 = $this->prophesize(Participant::class);

        $this->sheet1 = $this->prophesize(Sheet::class);
        $this->sheet2 = $this->prophesize(Sheet::class);
        $this->sheet3 = $this->prophesize(Sheet::class);

        $this->type1 = $this->prophesize(Type::class);
        $this->type2 = $this->prophesize(Type::class);
        $this->type1->getId()->willReturn(4321);
        $this->type2->getId()->willReturn(4322);

        $this->participant1->getSheet()->willReturn($this->sheet1->reveal());
        $this->participant2->getSheet()->willReturn($this->sheet2->reveal());
        $this->participant3->getSheet()->willReturn($this->sheet3->reveal());

        $this->sheet1->getType()->willReturn($this->type1->reveal());
        $this->sheet2->getType()->willReturn($this->type1->reveal());
        $this->sheet3->getType()->willReturn($this->type2->reveal());

        $this->product1 = $this->prophesize(Product::class);
        $this->product2 = $this->prophesize(Product::class);
        $this->product3 = $this->prophesize(Product::class);

        $this->product1->isParticipant()->willReturn(true);
        $this->product2->isParticipant()->willReturn(false);
        $this->product3->isParticipant()->willReturn(false);
        $this->product1->getName()->willReturn('product1');
        $this->product2->getName()->willReturn('product2');
        $this->product3->getName()->willReturn('product3');
        $this->product1->getId()->willReturn(123);
        $this->product2->getId()->willReturn(124);
        $this->product3->getId()->willReturn(125);
    }

    public function testHandle(): void
    {
        $participants = [
            $this->participant1->reveal(),
            $this->participant2->reveal(),
            $this->participant3->reveal(),
        ];

        $this->participantRepository->findByIds([1, 2, 3])->shouldBeCalled()->willReturn($participants);

        $products = [
            $this->product1->reveal(),
            $this->product2->reveal(),
            $this->product3->reveal(),
        ];
        $this->productRepository->findParticipantAndAttributableByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn($products)
        ;

        $templateData1 = $this->prophesize(TemplateData::class);
        $templateData2 = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createRegistrationFromType($this->type1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData1->reveal())
        ;
        $this->templateDataFactory
            ->createRegistrationFromType($this->type2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData2->reveal())
        ;

        $text1 = $this->prophesize(EditableText::class);
        $text2 = $this->prophesize(EditableText::class);
        $nomenclature1 = $this->prophesize(Nomenclature::class);

        $text1->getKey()->willReturn('AZERTY1');
        $text2->getKey()->willReturn('AZERTY1');
        $nomenclature1->getKey()->willReturn('AZERTY2');

        $text2->getExportableFieldname('fr', 'en')->shouldBeCalled()->willReturn('text');
        $nomenclature1->getExportableFieldname('fr', 'en')->shouldBeCalled()->willReturn('nomenclature');

        // Same key as $text2 so should not be called
        $text1->getExportableFieldname('fr', 'en')->shouldNotBeCalled();

        $exportableObject1 = [
            $text2->reveal(),
            $nomenclature1->reveal(),
        ];
        $exportableObject2 = [
            $text1->reveal(),
        ];

        $templateData1->getProfileObjects()->willReturn($exportableObject1);
        $templateData2->getProfileObjects()->willReturn($exportableObject2);

        $view1 = $this->prophesize(ParticipantView::class);
        $view2 = $this->prophesize(ParticipantView::class);
        $view3 = $this->prophesize(ParticipantView::class);
        $this->participantViewQueryHandler
            ->handle(new ParticipantViewQuery($this->event->reveal(), $this->participant1->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($view1->reveal())
        ;
        $this->participantViewQueryHandler
            ->handle(new ParticipantViewQuery($this->event->reveal(), $this->participant2->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($view2->reveal())
        ;
        $this->participantViewQueryHandler
            ->handle(new ParticipantViewQuery($this->event->reveal(), $this->participant3->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($view3->reveal())
        ;

        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(1);
        $happening->getTitle('fr')->willReturn('Conférence');

        $this->happeningRepository
            ->findListByEvent($this->event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([$happening->reveal()]);

        $this->requestRepository->loadParticipantRequestsCount([1, 2, 3])->shouldBeCalled();
        $this->meetingRepository->loadParticipantMeetingsCount([1, 2, 3])->shouldBeCalled();

        $query = new ExportQuery($this->event->reveal(), [1, 2, 3], 'fr');

        $handler = new ExportQueryHandler(
            $this->participantRepository->reveal(),
            $this->participantViewQueryHandler->reveal(),
            $this->templateDataFactory->reveal(),
            $this->productRepository->reveal(),
            $this->happeningRepository->reveal(),
            $this->requestRepository->reveal(),
            $this->meetingRepository->reveal()
        );

        $result = $handler->handle($query);

        $expected = new ParticipantListView(
            'fr',
            [
                $view1->reveal(),
                $view2->reveal(),
                $view3->reveal(),
            ],
            [
                'day_123' => '10/10/2018',
            ],
            [
                'AZERTY1' => 'text',
                'AZERTY2' => 'nomenclature',
            ],
            [
                'participant_123' => 'product1',
                'option_124' => 'product2',
                'option_125' => 'product3',
            ],
            [
                'happening_1' => 'Conférence'
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
