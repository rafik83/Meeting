<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;


use Psr\Log\LoggerInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\ThirdParty\CCIP\OrderCCIPViewQuery;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class OrderCCIPAction
{
    public const TEMPLATE = 'EventBundle:ThirdParty:CCIP/orderCCIP.xml.twig';

    private Environment $twig;

    private QueryBusInterface $queryBus;

    private LoggerInterface $logger;

    public function __construct(
        QueryBusInterface $queryBus,
        Environment $twig,
        LoggerInterface $logger
    )
    {
        $this->queryBus = $queryBus;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Order $order, EventDomain $eventDomain, User $user): Response
    {

        if($order->isCancelled() === true){
            $this->logger->info('cancel', [$order->isCancelled()]);
        }

        $orderView = $this->queryBus->handle(
            new OrderCCIPViewQuery($eventDomain->getEvent(), $request->getLocale(), $order, $user)
        );

        $xml = $this->twig->render(self::TEMPLATE, [
            'orderView' => $orderView
        ]);

        $response = new Response(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $xml));
        $response->setCharset('ISO-8859-1');
        $response->headers->set('content-type','application/xml');
        return $response;
    }

}
