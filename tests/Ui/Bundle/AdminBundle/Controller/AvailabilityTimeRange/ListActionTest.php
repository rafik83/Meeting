<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\AvailabilityTimeRange;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\ListViewQuery;
use Proximum\Vimeet\Application\View\AvailabilityTimeRange\ListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\AvailabilityTimeRange\ListAction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $authorizationChecker;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->engine = $this->prophesize(Environment::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
    }

    public function testInvokeNotGranted()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);
        $this->queryBus->handle(Argument::any())->shouldNotBeCalled();
        $this->engine->render(Argument::any())->shouldNotBeCalled();

         $action = new ListAction(
            $this->authorizationChecker->reveal(),
            $this->queryBus->reveal(),
            $this->engine->reveal()
        );

         $action($this->event->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $view = new ListView([]);
        $this->queryBus->handle(new ListViewQuery($this->event->reveal()))->shouldBeCalled()->willReturn($view);

        $this->engine
            ->render('AdminBundle:AvailabilityTimeRange:list.html.twig', [
                'event' => $this->event->reveal(),
                'list' => $view,
            ])
            ->shouldBeCalled()
            ->willReturn(new Response())
        ;

        $action = new ListAction(
            $this->authorizationChecker->reveal(),
            $this->queryBus->reveal(),
            $this->engine->reveal()
        );
        $result = $action($this->event->reveal());

        $this->assertInstanceOf(Response::class, $result);
    }
}
