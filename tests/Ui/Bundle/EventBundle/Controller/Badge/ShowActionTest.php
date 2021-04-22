<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Badge;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\UserBadgeByEventView;
use Proximum\Vimeet\Domain\Badge\AvailableChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\User\Sheet\HasAccessToSheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Badge\ShowAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ShowActionTest extends TestCase
{
    public function testShowBadge()
    {
        $event = $this->prophesize(Event::class);

        $eventDomain = $this->prophesize(EventDomain::class);
        $eventDomain->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $request = $this->prophesize(Request::class);

        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($user->reveal());

        $userBadgeByEventView = new UserBadgeByEventView(
            'Sheet title',
            'Korben',
            'Dallas',
            'Taxi driver',
            'Exhibitor',
            '000420001337',
            'qrCodeImageBase64',
            '/path/to/header.png',
            '#ffffff',
            '#000000',
            null,
            false,
            null,
            null,
            false,
            '#eee',
            '#000'
        );

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus
            ->handle(new GetUserBadgeByEventQuery($event->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn($userBadgeByEventView)
        ;

        $badgeAvailableChecker = $this->prophesize(AvailableChecker::class);
        $badgeAvailableChecker->isSatisfiedBy($sheet->reveal())->shouldBeCalled()->willReturn(true);

        $twig = $this->prophesize(Environment::class);
        $twig
            ->render('EventBundle:Badge:show.html.twig',
                [
                    'event' => $event->reveal(),
                    'sheet' => $sheet->reveal(),
                    'userBadgeByEventView' => $userBadgeByEventView,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('HTML of the badge')
        ;

        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $authorizationChecker->isGranted(SheetVoter::EDIT, $sheet->reveal())->shouldBeCalled()->willReturn(true);
        $hasAccessToSheet = $this->prophesize(HasAccessToSheet::class);
        $hasAccessToSheet->isSatisfiedBy($user->reveal(), $event->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $showAction = new ShowAction(
            $badgeAvailableChecker->reveal(),
            $authorizationChecker->reveal(),
            $hasAccessToSheet->reveal(),
            $twig->reveal(),
            $queryBus->reveal()
        );
        $response = $showAction($request->reveal(), $eventDomain->reveal(), $user->reveal(), $sheet->reveal());

        $this->assertInstanceOf(Response::class, $response);
    }
}
