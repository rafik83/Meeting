<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Payum\CCIP\PreparePayment;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class PreparePaymentAction
{
    public const TEMPLATE = 'EventBundle:ThirdParty:CCIP/preparePaymentCCIP.html.twig';

    private Environment $twig;
    private FunnelFactory $packageFunnelFactory;
    private PreparePayment $prepareCcipPayment;
    private OrderRepositoryInterface $orderRepository;
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    public string $ccipMode;
    public string $ccipFormAction;

    public function __construct(
        Environment $twig,
        FunnelFactory $packageFunnelFactory,
        PreparePayment $prepareCcipPayment,
        OrderRepositoryInterface $orderRepository,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        string $ccipMode,
        string $ccipFormAction
    ) {
        $this->twig = $twig;
        $this->packageFunnelFactory = $packageFunnelFactory;
        $this->prepareCcipPayment = $prepareCcipPayment;
        $this->orderRepository = $orderRepository;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->ccipMode = $ccipMode;
        $this->ccipFormAction = $ccipFormAction;
    }

    public function __invoke(
        Request $request,
        Sheet $sheet,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Transaction $transaction
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || $userDomain->getUser()->getId() !== $transaction->getUser()->getId()
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();
        $funnel = $this->packageFunnelFactory->create($sheet, $request->getLocale());

        $captureToken = $this->prepareCcipPayment->process($transaction, $request->getLocale());

        $orders = $this->orderRepository->findByIds(explode(',', $transaction->getInternalReference()));

        return new Response($this->twig->render(self::TEMPLATE, [
            'orders' => $orders,
            'sheet' => $sheet,
            'user' => $userDomain->getUser(),
            'domain' => $event->getDomain(),
            'view' => ['funnel' => $funnel],
            'event' => $eventDomain->getEvent(),
            'payumToken' => $captureToken->getHash(),
            'ccipMode' => $this->ccipMode,
            'ccipFormAction' => $this->ccipFormAction,
            'transactionId' => $transaction->getId()
        ]));
    }
}
