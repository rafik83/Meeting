<?php

namespace Proximum\Vimeet\Tests\Application\Query\Participant\Export;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Participant\Export\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Participant\Export\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\HasRemainingToPay;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\BooleanObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class ParticipantViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $happeningParticipationRepository;

    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $hasRemainingToPay;

    /** @var ObjectProphecy */
    private $translator;

    /** @var ObjectProphecy */
    private $productAttributedToParticipantRepository;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $requestRepository;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $chatSessionRepository;

    /** @var ObjectProphecy */
    private $scanRepository;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $participant;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $type;

    /** @var ObjectProphecy */
    private $participantProduct;

    /** @var ObjectProphecy */
    private $product1;

    /** @var ObjectProphecy */
    private $product2;

    /** @var ObjectProphecy */
    private $attributableProduct1;

    /** @var ObjectProphecy */
    private $attributableProduct2;

    /** @var \DateTimeInterface */
    private $dateTime, $dateTime2;

    /** @var ObjectProphecy */
    private $day, $day2;

    public function setUp()
    {
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->hasRemainingToPay = $this->prophesize(HasRemainingToPay::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->productAttributedToParticipantRepository = $this->prophesize(ProductAttributedToParticipantRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);

        $this->participant = $this->prophesize(Participant::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->type = $this->prophesize(Type::class);

        $this->participant->getSheet()->willReturn($this->sheet->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->participant->getUser()->willReturn($this->user->reveal());
        $this->participant->getLocale()->willReturn('es');
        $this->dateTime = new \DateTime('2017-10-09 13:39:34.000');
        $this->dateTime2 = new \DateTime('2017-10-10 13:39:34.000');
        $this->sheet->getCreatedAt()->willReturn($this->dateTime);
        $this->event->getTimeZone()->willReturn('Europe/Paris');
        $this->event->getAvailableLocale('fr')->willReturn('fr');
        $this->day = $this->prophesize(Event\Day::class);
        $this->day2 = $this->prophesize(Event\Day::class);
        $this->day->getId()->willReturn(123);
        $this->day->getBegin()->willReturn($this->dateTime);
        $this->day2->getId()->willReturn(124);
        $this->day2->getBegin()->willReturn($this->dateTime2);
        $this->event->getDays()->willReturn([$this->day->reveal(), $this->day2->reveal()]);

        $this->participantProduct = $this->prophesize(Product::class);
        $this->participant->getParticipantProduct()->willReturn($this->participantProduct->reveal());
        $this->participantProduct->getId()->willReturn(12345);

        $this->product1 = $this->prophesize(Product::class);
        $this->product2 = $this->prophesize(Product::class);
        $this->attributableProduct1 = $this->prophesize(ProductAttributedToParticipant::class);
        $this->attributableProduct2 = $this->prophesize(ProductAttributedToParticipant::class);

        $this->product1->getId()->willReturn(123456);
        $this->product2->getId()->willReturn(123457);
        $this->attributableProduct1->getProduct()->willReturn($this->product1->reveal());
        $this->attributableProduct2->getProduct()->willReturn($this->product2->reveal());

        $this->scanRepository = $this->prophesize(ScanRepositoryInterface::class);
    }

    public function testHandle(): void
    {
        $this->sheet->getId()->willReturn(12);
        $this->participant->getId()->willReturn(1234);
        $this->user->getId()->willReturn(123);
        $this->type->getTitle('fr')->willReturn('typeTitle');
        $this->sheet->getTitle()->willReturn('sheetTitle');
        $this->sheet->isEnabled()->willReturn(true);
        $this->participant->getEmail()->willReturn('email@example.net');

        $this->hasRemainingToPay
            ->isSatisfiedBy($this->sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->happeningParticipationRepository
            ->hasParticipationForUserAndEvent(
                $this->user->reveal(),
                $this->event->reveal()
            )->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->productAttributedToParticipantRepository
            ->findByParticipant($this->participant->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $this->attributableProduct1->reveal(),
                $this->attributableProduct2->reveal(),
            ])
        ;

        $boolean = $this->prophesize(BooleanObject::class);
        $gender = $this->prophesize(Gender::class);
        $text = $this->prophesize(EditableText::class);

        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getProfileObjects()
            ->shouldBeCalled()
            ->willReturn([
                $boolean->reveal(),
                $gender->reveal(),
                $text->reveal()
            ])
        ;

        $boolean->getKey()->willReturn('AZERTY1');
        $gender->getKey()->willReturn('AZERTY2');
        $text->getKey()->willReturn('AZERTY3');
        $boolean->getExportableContent([], 'fr')->willReturn(true);
        $gender->getExportableContent([], 'fr')->willReturn('woman');
        $text->getExportableContent([], 'fr')->willReturn('this is a test');

        $this->translator
            ->trans('gender.woman', [], null, 'fr')
            ->shouldBeCalled()
            ->willReturn('Femme')
        ;

        $this->translator
            ->trans('boolean.yes', [], null, 'fr')
            ->shouldBeCalled()
            ->willReturn('Oui')
        ;

        $this->templateDataFactory
            ->createRegistrationFromParticipant($this->participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;

        $check = $this->prophesize(User\Event\Scan::class);
        $check->getScannedAt()->willReturn($this->dateTime);
        $this->scanRepository
            ->getUserFirstCheckinTodayByEvent($this->user->reveal(), $this->event->reveal(), $this->dateTime)
            ->shouldBeCalled()
            ->willReturn($check)
        ;

        $this->scanRepository
            ->getUserFirstCheckinTodayByEvent($this->user->reveal(), $this->event->reveal(), $this->dateTime2)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $query = new ParticipantViewQuery(
            $this->event->reveal(),
            $this->participant->reveal(),
            'fr'
        );

        $scan = $this->prophesize(User\Event\Scan::class);
        $scan->getScannedAt()->willReturn(new \DateTime('2018-10-09 13:38:34.000'));

        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(1);
        $happening->getTitle('fr')->willReturn('Conférence');

        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $happeningRepository->findListByEvent($this->event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([$happening]);

        $this->scanRepository->getScanForUserEventTypeAndObjectId($this->user->reveal(), $this->event->reveal(), \Proximum\Vimeet\Domain\Scan\Type::TYPE_HAPPENING_ENTRANCE, 1)
            ->shouldBeCalled()
            ->willReturn($scan);

        $this->sheetRepository->getAnalyticsByUser($this->event->reveal())
            ->shouldBeCalledOnce()
            ->willReturn([123 => ['viewedSheets' => 42, 'clickedElements' => 24]]);

        $this->requestRepository->getParticipantRequestsCount($this->participant->reveal())
            ->shouldBeCalled()
            ->willReturn(5);

        $this->meetingRepository->getParticipantMeetingsCount($this->participant->reveal())
            ->shouldBeCalled()
            ->willReturn(3);

        $this->chatSessionRepository->countCallVisioByEventAndUser($this->event->reveal(), $this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(7);

        $handler = new ParticipantViewQueryHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->templateDataFactory->reveal(),
            $this->hasRemainingToPay->reveal(),
            $this->translator->reveal(),
            $this->productAttributedToParticipantRepository->reveal(),
            $this->scanRepository->reveal(),
            $happeningRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->requestRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->chatSessionRepository->reveal()
        );

        $result = $handler->handle($query);

        $expected = new ParticipantView(
            12,
            'typeTitle',
            'sheetTitle',
            true,
            123,
            1234,
            'email@example.net',
            '2017-10-09',
            false,
            true,
            12345,
            [
                'day_123' => '09/10/2017 15:39',
                'day_124' => null,
            ],
            [
                'option_123456' => 123456,
                'option_123457' => 123457,
            ],
            [
                'AZERTY1' => 'Oui',
                'AZERTY2' => 'Femme',
                'AZERTY3' => 'this is a test',
            ],
            [
                'happening_1' => '09/10/2018 15:38'
            ],
            42,
            24,
            5,
            3,
            7,
            'es'
        );

        $this->assertEquals($expected, $result);
    }
}
