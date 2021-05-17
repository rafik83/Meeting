<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\CCIP;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Payum\CCIP\CapturePayment;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OrderCCIPValidAction
{

    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;
    private RouterInterface $router;
    private FlashBagInterface $flashBag;
    private CapturePayment $captureCcipPayment;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        CapturePayment $captureCcipPayment
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->captureCcipPayment = $captureCcipPayment;
    }

    public function __invoke(
        Sheet $sheet,
        string $captureToken,
        string $paymentNumber
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $this->captureCcipPayment->processValid($captureToken, $paymentNumber);

        $this->flashBag->add('success', 'flash.payment.success');

        return new RedirectResponse($this->router->generate('event_order_list', ['sheet' => $sheet->getId()]));
    }
}
