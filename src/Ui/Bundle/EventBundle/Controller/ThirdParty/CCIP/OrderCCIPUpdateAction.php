<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;


use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderCCIPUpdateAction
{

    private QueryBusInterface $queryBus;

    public function __construct(
        QueryBusInterface $queryBus
    )
    {
        $this->queryBus = $queryBus;
    }

    public function __invoke(Request $request, Order $order, EventDomain $eventDomain, User $user): Response
    {
        return new Response();
    }
}
