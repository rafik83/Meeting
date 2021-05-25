<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;


use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Payum\CCIP\CapturePayment;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OrderCCIPCancelErrorAction
{

    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private RouterInterface $router;
    private FlashBagInterface $flashBag;
    private CapturePayment $captureCcipPayment;
    private ?LoggerInterface $logger;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        CapturePayment $captureCcipPayment,
        ?LoggerInterface $logger
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->captureCcipPayment = $captureCcipPayment;
        $this->logger = $logger;
    }

    public function __invoke(
        Request $request,
        Sheet $sheet,
        string $captureToken
    ): RedirectResponse {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $this->captureCcipPayment->processCancel($captureToken);

        $this->flashBag->add('error', 'flash.payment.error');

        if ($this->logger && $request->query->has('error')) {
            $this->logger->error('CCIP payment error during payment {errorCode}', [
                'errorCode' => $request->query->get('error'),
                'captureToken' => $captureToken,
            ]);
        }

        return new RedirectResponse($this->router->generate('event_order_list', ['sheet' => $sheet->getId()]));
    }
}
