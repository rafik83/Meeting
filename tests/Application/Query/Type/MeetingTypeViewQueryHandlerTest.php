<?php

namespace Proximum\Vimeet\Tests\Application\Query\Type;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Type\MeetingTypeViewQuery;
use Proximum\Vimeet\Application\Query\Type\MeetingTypeViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class MeetingTypeViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $typeRepository;

    /** @var MeetingTypeViewQueryHandler */
    private $handler;

    /** @var Event */
    private $event;

    /** @var Sheet */
    private $sheet;

    /** @var MeetingTypeViewQuery */
    private $query;

    /** @var Type */
    private $type;

    /** @var string */
    private $locale;

    public function setUp()
    {
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $this->locale = 'fr';

        $this->handler = new MeetingTypeViewQueryHandler(
            $this->typeRepository->reveal()
        );

        $this->event = EventFactory::createEvent();
        $this->sheet = SheetFactory::create($this->event);
        $this->query = new MeetingTypeViewQuery($this->sheet, $this->locale);

        $this->type = $this->prophesize(Type::class);
    }

    public function testHandle()
    {
        $this->type->getId()->shouldBeCalled()->willReturn(1);
        $this->type->getTitle('fr')->shouldBeCalled()->willReturn('type title');
        $this->type->isHidden()->shouldBeCalled()->willReturn(false);

        $expectedTypeView = new TypeView(1, 'type title', '', false);

        $this->typeRepository
            ->getFromSheetMeetingRequests($this->sheet, $this->locale)
            ->shouldBeCalled()
            ->willReturn([$this->type]);

        $typeViews = $this->handler->handle($this->query);
        $this->assertEquals([$expectedTypeView], $typeViews);
    }
}
