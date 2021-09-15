<?php

namespace Proximum\Vimeet\Tests\Application\Query\Type;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Type\TypesWithPaymentConditionsViewQuery;
use Proximum\Vimeet\Application\Query\Type\TypesWithPaymentConditionsViewQueryHandler;
use Proximum\Vimeet\Application\View\Type\TypesWithPaymentConditionsView;
use Proximum\Vimeet\Application\View\Type\TypeWithPaymentConditionsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypesWithPaymentConditionsViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $typeView1 = new TypeWithPaymentConditionsView('toto');
        $typeView2 = new TypeWithPaymentConditionsView('tata');

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $type1->getTitle('fr')->willReturn('toto');
        $type2->getTitle('fr')->willReturn('tata');

        $typeRepository
            ->getTypesWithPaymentConditionsByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$type1->reveal(), $type2->reveal()])
        ;

        $query = new TypesWithPaymentConditionsViewQuery($event->reveal(), 'fr');
        $handler = new TypesWithPaymentConditionsViewQueryHandler($typeRepository->reveal());

        $result = $handler->handle($query);

        $expected = new TypesWithPaymentConditionsView([$typeView1, $typeView2]);

        $this->assertEquals($expected, $result);
    }
}
