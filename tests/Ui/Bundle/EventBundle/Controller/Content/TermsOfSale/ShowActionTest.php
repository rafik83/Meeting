<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Content\TermsOfSale;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Domain\View\Content\TermsOfSaleView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Content\TermsOfSale\ShowAction;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ShowActionTest extends TestCase
{
    private $sheet;
    private $event;
    private $eventLocale;
    private $content;
    private $response;
    private $request;
    private $eventDomain;
    private $query;
    private $view;
    private $engine;
    private $authorizationChecker;
    private $queryBus;

    public function setUp()
    {
        $this->sheet = SheetFactory::create();
        $this->event = EventFactory::createEvent();

        $this->eventLocale          = 'fr';
        $this->content              = 'foobar';
        $this->response             = $this->prophesize(Response::class);
        $this->request              = $this->prophesize(Request::class);
        $this->eventDomain          = $this->prophesize(EventDomain::class);
        $this->query                = new TermsOfSaleViewQuery($this->event, $this->sheet, $this->eventLocale);
        $this->view                 = new TermsOfSaleView($this->content);
        $this->engine               = $this->prophesize(EngineInterface::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->queryBus             = $this->prophesize(QueryBusInterface::class);
    }

    public function testInvoke()
    {
        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet)->shouldBeCalled()->willReturn(true);

        $this->eventDomain->getEvent()->shouldBeCalled()->willReturn($this->event);
        $this->request->getLocale()->shouldBeCalled()->willReturn($this->eventLocale);

        $this->engine->renderResponse('EventBundle:Content:terms-of-sale.html.twig',
            [
                'sheet'   => $this->sheet,
                'event'   => $this->event,
                'content' => 'foobar',
            ])->shouldBeCalled()->willReturn($this->response->reveal());

        $this->queryBus->handle($this->query)->shouldBeCalled()->willReturn($this->view);

        $action   = new ShowAction(
            $this->engine->reveal(),
            $this->queryBus->reveal(),
            $this->authorizationChecker->reveal()
        );
        $response = $action($this->request->reveal(), $this->eventDomain->reveal(), $this->sheet);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationChecker->isGranted(SheetVoter::EDIT, $this->sheet)->shouldBeCalled()->willReturn(false);

        $this->eventDomain->getEvent()->shouldNotBeCalled();
        $this->request->getLocale()->shouldNotBeCalled();

        $this->engine->renderResponse('EventBundle:Content:terms-of-sale.html.twig',
            [
                'sheet'   => $this->sheet,
                'event'   => $this->event,
                'content' => 'foobar',
            ]
        )->shouldNotBeCalled();

        $this->queryBus->handle($this->query)->shouldNotBeCalled();

        $action = new ShowAction(
            $this->engine->reveal(),
            $this->queryBus->reveal(),
            $this->authorizationChecker->reveal()
        );
        $action($this->request->reveal(), $this->eventDomain->reveal(), $this->sheet);
    }
}
