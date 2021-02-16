<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Type;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Type\TypeViewQuery;
use Proximum\Vimeet\Application\View\Type\TypeListsView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\ListAction;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $engine;

    public function setUp()
    {
        $this->event                       = $this->prophesize(Event::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->queryBus                    = $this->prophesize(QueryBusInterface::class);
        $this->engine                      = $this->prophesize(EngineInterface::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $request = new Request();

        $action = new ListAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->queryBus->reveal(),
            $this->engine->reveal()
        );
        $action($request, $this->event->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('de');
        $request = new Request(['page' => 12]);
        $request->setLocale('fr');

        $view = $this->prophesize(TypeListsView::class);
        $this->queryBus
            ->handle(new TypeViewQuery(12, $this->event->reveal(), 'de'))
            ->shouldBeCalled()
            ->willReturn($view->reveal())
        ;
        $this->engine
            ->renderResponse(ListAction::TEMPLATE, ['event' => $this->event->reveal(), 'types' => $view->reveal()])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new ListAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->queryBus->reveal(),
            $this->engine->reveal()
        );
        $result = $action($request, $this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }
}
