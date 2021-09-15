<?php

namespace Application\Query\Sheet\Group\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\ImpersonateUrlGeneratorInterface;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\AdminGroupViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Group\Admin\AdminGroupViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Group\Admin\GroupView;
use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\AdminFactory;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class GroupViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $admin = AdminFactory::create();
        $event = EventFactory::createEvent();

        $user = $this->prophesize(User::class);
        $user->getEmail()->shouldBeCalled()->willReturn('john@email.com');
        $user->getId()->shouldBeCalled()->willReturn(42);

        $reflectionGroup = new \ReflectionClass(Group::class);
        $propertyGroupId = $reflectionGroup->getProperty('id');
        $propertyGroupId->setAccessible(true);

        $group = new Group($event, $user->reveal(), 'My entity', false, $dateTime);
        $propertyGroupId->setValue($group, 1);

        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheetId = $reflectionSheet->getProperty('id');
        $propertySheetId->setAccessible(true);

        $sheet1 = SheetFactory::create($event);
        $sheet1->setTitle('Sheet title 1');
        $propertySheetId->setValue($sheet1, 1);

        $sheet2 = SheetFactory::create($event);
        $sheet2->setTitle('Sheet title 2');
        $propertySheetId->setValue($sheet2, 2);

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $impersonateUrlGenerator = $this->prophesize(ImpersonateUrlGeneratorInterface::class);

        $expectedGroupView = new GroupView(
            1,
            'My entity',
            'john@email.com',
            42,
            [
                new SheetView(1, 'Sheet title 1'),
                new SheetView(2, 'Sheet title 2'),
            ],
            $dateTime
        );

        $sheetRepository->getByGroup($group)->shouldBeCalled()->willReturn([$sheet1, $sheet2]);

        $impersonateUrlGenerator->generate(
            $admin,
            $user,
            $event,
            'event_sheet_group_index',
            ['sheetGroup' => 1]
        )->shouldBeCalled()->willReturn('_IMPERSONATE_LINK_');

        $handler = new AdminGroupViewQueryHandler($sheetRepository->reveal(), $impersonateUrlGenerator->reveal());

        $groupView = $handler->handle(new AdminGroupViewQuery($group, $admin));

        $this->assertEquals($expectedGroupView, $groupView);
    }
}
