<?php

namespace Proximum\Vimeet\Tests\Application\Query\Rooming\RoomingList\Export;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\UserViewQuery;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\UserViewQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Rooming\ExportList\UserSheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class UserViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $user = $this->prophesize(User::class);
        $account = $this->prophesize(User\Account::class);
        $event = $this->prophesize(Event::class);
        $locale = 'fr';
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $spot = $this->prophesize(Spot::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $merger = $this->prophesize(Merger::class);

        $order1 = $this->prophesize(Order::class);
        $order2 = $this->prophesize(Order::class);
        $order3 = $this->prophesize(Order::class);

        $plan1 = $this->prophesize(Product::class);
        $plan2 = $this->prophesize(Product::class);
        $plan3 = $this->prophesize(Product::class);

        $merger->getMergedOrders($sheet1->reveal())->shouldBeCalled()->willReturn($order1->reveal());
        $merger->getMergedOrders($sheet2->reveal())->shouldBeCalled()->willReturn($order2->reveal());
        $merger->getMergedOrders($sheet3->reveal())->shouldBeCalled()->willReturn($order3->reveal());

        $order1->getPlan()->shouldBeCalled()->willReturn($plan1->reveal());
        $order2->getPlan()->shouldBeCalled()->willReturn($plan2->reveal());
        $order3->getPlan()->shouldBeCalled()->willReturn($plan3->reveal());

        $commentExtraData = $this->prophesize(User\Event\ExtraData::class);
        $tastingExtraData = $this->prophesize(User\Event\ExtraData::class);

        $commentExtraData->getValue()->shouldBeCalled()->willReturn('This is a comment');
        $tastingExtraData->getValue()->shouldBeCalled()->willReturn('This is a tasting');

        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);

        $sheet1->getTitle()->shouldBeCalled()->willReturn('Aanera');
        $sheet2->getTitle()->shouldBeCalled()->willReturn('Bbnera');
        $sheet3->getTitle()->shouldBeCalled()->willReturn('Ccnera');

        $sheet1->getType()->shouldBeCalled()->willReturn($type1->reveal());
        $sheet2->getType()->shouldBeCalled()->willReturn($type2->reveal());
        $sheet3->getType()->shouldBeCalled()->willReturn($type1->reveal());

        $sheet1->getTypeTitle('fr')->shouldBeCalled()->willReturn('Exposant');
        $sheet2->getTypeTitle('fr')->shouldBeCalled()->willReturn('Visiteur');

        $type1->getId()->shouldBeCalled()->willReturn(11);
        $type2->getId()->shouldBeCalled()->willReturn(12);

        $sheet1->getSpot()->shouldBeCalled()->willReturn(null);
        $sheet2->getSpot()->shouldBeCalled()->willReturn(null);
        $sheet3->getSpot()->shouldBeCalled()->willReturn($spot->reveal());

        $sheet1->getFollowerName()->shouldBeCalled()->willReturn('Al Pacino');
        $sheet2->getFollowerName()->shouldBeCalled()->willReturn('Robert DeNiro');
        $sheet3->getFollowerName()->shouldBeCalled()->willReturn('Joe Pesci');

        $plan1->getName()->shouldBeCalled()->willReturn('Plan 1');
        $plan2->getName()->shouldBeCalled()->willReturn('Plan 2');
        $plan3->getName()->shouldBeCalled()->willReturn('Plan 3');

        $spot->getReference()->shouldBeCalled()->willReturn('A123');

        $user->getId()->shouldBeCalled()->willReturn(1);
        $user->getEmail()->shouldBeCalled()->willReturn('test@test.com');
        $user->getMobile()->shouldBeCalled()->willReturn('0000000001');
        $user->getAccount()->shouldBeCalled()->willReturn($account->reveal());

        $account->getGender()->shouldBeCalled()->willReturn('man');
        $account->getFirstName()->shouldBeCalled()->willReturn('Jean');
        $account->getLastName()->shouldBeCalled()->willReturn('Paul');

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $sheetRepository->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()])
        ;

        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), ExtraDataType::ROOMING_COMMENT, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($commentExtraData->reveal())
        ;

        $extraDataRepository
            ->getExtraDataForEventNameAndUser($event->reveal(), ExtraDataType::ROOMING_TASTING, $user->reveal())
            ->shouldBeCalled()
            ->willReturn($tastingExtraData->reveal())
        ;

        $query = new UserViewQuery($event->reveal(), $user->reveal(), $locale);
        $handler = new UserViewQueryHandler(
            $sheetRepository->reveal(),
            $extraDataRepository->reveal(),
            $merger->reveal()
        );

        $result = $handler->handle($query);

        $expected = new UserSheetView(
            1,
            'man',
            'Jean',
            'Paul',
            'test@test.com',
            '0000000001',
            '1,2,3',
            'Aanera,Bbnera,Ccnera',
            'Al Pacino,Robert DeNiro,Joe Pesci',
            'Plan 1,Plan 2,Plan 3',
            'Exposant,Visiteur',
            'A123',
            'This is a comment',
            'This is a tasting'
        );

        $this->assertEquals($expected, $result);
    }
}
