<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;


use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;
use Proximum\Vimeet\Infrastructure\Payum\CCIP\PreparePayment;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PreparePaymentAction
{
    public const TEMPLATE = 'EventBundle:ThirdParty:CCIP/preparePaymentCCIP.html.twig';

    private Environment $twig;
    private FunnelFactory $packageFunnelFactory;
    private PreparePayment $prepareCcipPayment;
    public string $ccipMode;
    public string $ccipFormAction;

    public function __construct(
        Environment $twig,
        FunnelFactory $packageFunnelFactory,
        PreparePayment $prepareCcipPayment,
        string $ccipMode,
        string $ccipFormAction
    ) {
        $this->twig = $twig;
        $this->packageFunnelFactory = $packageFunnelFactory;
        $this->prepareCcipPayment = $prepareCcipPayment;
        $this->ccipMode = $ccipMode;
        $this->ccipFormAction = $ccipFormAction;
    }

    public function __invoke(
        Request $request,
        Order $order,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Transaction $transaction
    ): Response {

        // TODO: check that order and transaction belong to current user

        $event = $eventDomain->getEvent();
        $funnel = $this->packageFunnelFactory->create($order->getSheet(), $request->getLocale());

        $captureToken = $this->prepareCcipPayment->process($transaction, $request->getLocale());

        return new Response($this->twig->render(self::TEMPLATE, [
            'order' => $order,
            'sheet' => $order->getSheet(),
            'user' => $userDomain->getUser(),
            'domain' => $event->getDomain(),
            'view' => ['funnel' => $funnel],
            'event' => $eventDomain->getEvent(),
            'payumToken' => $captureToken->getHash(),
            'ccipMode'=> $this->ccipMode,
            'ccipFormAction' => $this->ccipFormAction
        ]));
    }
}
