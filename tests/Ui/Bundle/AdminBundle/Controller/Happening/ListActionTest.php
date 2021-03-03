<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningListViewQuery;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\ListAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $authorizationAccessChecker;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->authorizationAccessChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->twig = $this->prophesize(Environment::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationAccessChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new ListAction(
            $this->authorizationAccessChecker->reveal(),
            $this->queryBus->reveal(),
            $this->twig->reveal()
        );

        $action($this->request->reveal(), $this->event->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationAccessChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->request->getLocale()->willReturn('de');
        $this->event->getAvailableLocale('de')->willReturn('fr');
        $list = $this->prophesize(HappeningListView::class);
        $this->queryBus
            ->handle(new HappeningListViewQuery($this->event->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($list->reveal());

        $this->twig
            ->render(ListAction::TEMPLATE, [
                'event' => $this->event->reveal(),
                'list' => $list->reveal(),
            ])
            ->shouldBeCalled()
            ->willReturn(new Response());

        $action = new ListAction(
            $this->authorizationAccessChecker->reveal(),
            $this->queryBus->reveal(),
            $this->twig->reveal()
        );

        $result = $action($this->request->reveal(), $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }
}
