<?php

namespace Proximum\Vimeet\Tests\Application\Query\Operator;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Operator\OperatorListViewQuery;
use Proximum\Vimeet\Application\Query\Operator\OperatorListViewQueryHandler;
use Proximum\Vimeet\Application\View\Operator\OperatorListView;
use Proximum\Vimeet\Application\View\Operator\OperatorView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;

class OperatorListViewQueryHandlerTest extends TestCase
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

        $operator1 = $this->prophesize(Admin::class);
        $operator2 = $this->prophesize(Admin::class);
        $operator3 = $this->prophesize(Admin::class);
        $date1 = new \DateTime('2018-11-28 10:00:00.000');
        $date2 = new \DateTime('2018-11-26 14:30:20.100');

        $operator1->getId()->shouldBeCalled()->willReturn(11);
        $operator1->getFirstname()->shouldBeCalled()->willReturn('First Name 1');
        $operator1->getLastname()->shouldBeCalled()->willReturn('Last Name 1');
        $operator1->getEmail()->shouldBeCalled()->willReturn('email1@example.net');
        $operator1->getLastLoginAt()->shouldBeCalled()->willReturn($date1);
        $operator1->getRole()->shouldBeCalled()->willReturn(Admin::ROLE_OPERATOR);
        $operator1->getEvents()->shouldBeCalled()->willReturn(new ArrayCollection([$event1->reveal(), $event2->reveal()]));

        $operator2->getId()->shouldBeCalled()->willReturn(12);
        $operator2->getFirstname()->shouldBeCalled()->willReturn('First Name 2');
        $operator2->getLastname()->shouldBeCalled()->willReturn('Last Name 2');
        $operator2->getEmail()->shouldBeCalled()->willReturn('email2@example.net');
        $operator2->getLastLoginAt()->shouldBeCalled()->willReturn(null);
        $operator2->getRole()->shouldBeCalled()->willReturn(Admin::ROLE_OPERATOR);
        $operator2->getEvents()->shouldBeCalled()->willReturn(new ArrayCollection([$event2->reveal(), $event3->reveal()]));

        $operator3->getId()->shouldBeCalled()->willReturn(13);
        $operator3->getFirstname()->shouldBeCalled()->willReturn('First Name 3');
        $operator3->getLastname()->shouldBeCalled()->willReturn('Last Name 3');
        $operator3->getEmail()->shouldBeCalled()->willReturn('email3@example.net');
        $operator3->getLastLoginAt()->shouldBeCalled()->willReturn($date2);
        $operator3->getRole()->shouldBeCalled()->willReturn(Admin::ROLE_PARTNER);
        $operator3->getEvents()->shouldBeCalled()->willReturn(new ArrayCollection([$event3->reveal()]));

        $admin = $this->prophesize(Admin::class);
        $filters = ['event' => null,];
        $adminRepository->getOperatorForOrganizer($admin->reveal(), $filters)->shouldBeCalled()->willReturn([
            $operator1->reveal(),
            $operator2->reveal(),
            $operator3->reveal(),
        ]);

        $query = new OperatorListViewQuery($admin->reveal(), $filters);
        $handler = new OperatorListViewQueryHandler($adminRepository->reveal());
        $result = $handler->handle($query);

        $views = [
            new OperatorView(
                11,
                'Last Name 1',
                'First Name 1',
                'email1@example.net',
                Admin::ROLE_OPERATOR,
                [
                    'Event 1',
                    'Event 2',
                ],
                $date1
            ),
            new OperatorView(
                12,
                'Last Name 2',
                'First Name 2',
                'email2@example.net',
                Admin::ROLE_OPERATOR,
                [
                    'Event 2',
                    'Event 3',
                ],
                null
            ),
            new OperatorView(
                13,
                'Last Name 3',
                'First Name 3',
                'email3@example.net',
                Admin::ROLE_PARTNER,
                [
                    'Event 3',
                ],
                $date2
            ),
        ];
        $expected = new OperatorListView($views);

        $this->assertEquals($expected, $result);
    }
}
