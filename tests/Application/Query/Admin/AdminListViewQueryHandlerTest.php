<?php

namespace Proximum\Vimeet\Tests\Application\Query\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Admin\AdminListViewQuery;
use Proximum\Vimeet\Application\Query\Admin\AdminListViewQueryHandler;
use Proximum\Vimeet\Application\View\Admin\AdminListView;
use Proximum\Vimeet\Application\View\Admin\AdminView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class AdminListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $adminRepository = $this->prophesize(AdminRepositoryInterface::class);

        $event1 = $this->prophesize(Event::class);
        $event2 = $this->prophesize(Event::class);
        $event3 = $this->prophesize(Event::class);
        $event1->getTitle()->shouldBeCalled()->willReturn('Event 1');
        $event2->getTitle()->shouldBeCalled()->willReturn('Event 2');
        $event3->getTitle()->shouldBeCalled()->willReturn('Event 3');

        $organizer1 = $this->prophesize(Admin::class);
        $organizer2 = $this->prophesize(Admin::class);
        $organizer3 = $this->prophesize(Admin::class);

        $organizer1->getId()->shouldBeCalled()->willReturn(11);
        $organizer1->getFirstname()->shouldBeCalled()->willReturn('First Name 1');
        $organizer1->getLastname()->shouldBeCalled()->willReturn('Last Name 1');
        $organizer1->getEmail()->shouldBeCalled()->willReturn('email1@example.net');
        $organizer1->getRole()->shouldBeCalled()->willReturn(Admin::ROLE_ORGANIZER);
        $organizer1->getEvents()->shouldBeCalled()->willReturn(new ArrayCollection([$event1->reveal(), $event2->reveal()]));

        $organizer2->getId()->shouldBeCalled()->willReturn(12);
        $organizer2->getFirstname()->shouldBeCalled()->willReturn('First Name 2');
        $organizer2->getLastname()->shouldBeCalled()->willReturn('Last Name 2');
        $organizer2->getEmail()->shouldBeCalled()->willReturn('email2@example.net');
        $organizer2->getRole()->shouldBeCalled()->willReturn(Admin::ROLE_ORGANIZER);
        $organizer2->getEvents()->shouldBeCalled()->willReturn(new ArrayCollection([$event2->reveal(), $event3->reveal()]));

        $organizer3->getId()->shouldBeCalled()->willReturn(13);
        $organizer3->getFirstname()->shouldBeCalled()->willReturn('First Name 3');
        $organizer3->getLastname()->shouldBeCalled()->willReturn('Last Name 3');
        $organizer3->getEmail()->shouldBeCalled()->willReturn('email3@example.net');
        $organizer3->getRole()->shouldBeCalled()->willReturn(Admin::ROLE_ORGANIZER);
        $organizer3->getEvents()->shouldBeCalled()->willReturn(new ArrayCollection([$event3->reveal()]));

        $filters = [
            'role' => Admin::ROLE_ORGANIZER,
        ];
        $adminRepository->list($filters)->shouldBeCalled()->willReturn([
            $organizer1->reveal(),
            $organizer2->reveal(),
            $organizer3->reveal(),
        ]);

        $query = new AdminListViewQuery($filters);
        $handler = new AdminListViewQueryHandler($adminRepository->reveal());
        $result = $handler->handle($query);

        $views = [
            new AdminView(
                11,
                'Last Name 1',
                'First Name 1',
                'email1@example.net',
                Admin::ROLE_ORGANIZER,
                [
                    'Event 1',
                    'Event 2',
                ]
            ),
            new AdminView(
                12,
                'Last Name 2',
                'First Name 2',
                'email2@example.net',
                Admin::ROLE_ORGANIZER,
                [
                    'Event 2',
                    'Event 3',
                ]
            ),
            new AdminView(
                13,
                'Last Name 3',
                'First Name 3',
                'email3@example.net',
                Admin::ROLE_ORGANIZER,
                [
                    'Event 3',
                ]
            ),
        ];
        $expected = new AdminListView($views);

        $this->assertEquals($expected, $result);
    }
}
