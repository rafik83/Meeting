<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;


use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\ThirdParty\CCIP\OrderCCIPViewQuery;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PreparePaymentAction
{
    public const TEMPLATE = 'EventBundle:ThirdParty:CCIP/preparePaymentCCIP.html.twig';

    private QueryBusInterface $queryBus;

    private Environment $twig;

    private FunnelFactory $packageFunnelFactory;

    public function __construct(
        QueryBusInterface $queryBus,
        Environment $twig,
        FunnelFactory $packageFunnelFactory
    )
    {
        $this->queryBus = $queryBus;
        $this->twig = $twig;
        $this->packageFunnelFactory = $packageFunnelFactory;
    }

    public function __invoke(Request $request, Order $order, EventDomain $eventDomain, User $user): Response
    {

        $event = $eventDomain->getEvent();
        $funnel = $this->packageFunnelFactory->create($order->getSheet(), $request->getLocale());

        return new Response($this->twig->render(self::TEMPLATE, [
            'order' => $order,
            'sheet' => $order->getSheet(),
            'user' => $user,
            'domain' => $event->getDomain(),
            'view' => ['funnel' => $funnel],
            'event' => $eventDomain->getEvent(),
        ]));
    }
}
