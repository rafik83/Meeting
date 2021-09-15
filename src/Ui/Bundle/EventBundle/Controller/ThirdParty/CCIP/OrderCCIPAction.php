<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;


use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\ThirdParty\CCIP\OrderCCIPViewQuery;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

    public function __invoke(Request $request, Transaction $transaction, EventDomain $eventDomain): Response
    {
        $captureToken = $this->getCaptureToken($request->query->get('xmlKey', ''));

        $orderView = $this->queryBus->handle(
            new OrderCCIPViewQuery($eventDomain->getEvent(), $request->getLocale(), $transaction, $transaction->getUser(), $captureToken)
        );

        $xml = $this->twig->render(self::TEMPLATE, [
            'orderView' => $orderView,
        ]);

        $response = new Response(iconv("UTF-8", "ISO-8859-1//TRANSLIT", $xml));
        $response->setCharset('ISO-8859-1');
        $response->headers->set('content-type', 'application/xml');

        return $response;
    }

    private function getCaptureToken(string $xmlKey): string
    {
        $hash = md5('r34_rpack');
        if (strpos($xmlKey, $hash) !== 0) {
            throw new AccessDeniedHttpException('Invalid xmlKey parameter');
        }

        return substr($xmlKey, strlen($hash));
    }
}
