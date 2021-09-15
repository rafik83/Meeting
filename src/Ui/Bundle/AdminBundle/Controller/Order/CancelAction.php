<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Order;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Order\CancelAll;
use Proximum\Vimeet\Domain\Exception\Order\OrderCanNotBeCancelledException;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CancelAction
{
    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        CsrfTokenManagerInterface $csrfTokenManager,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function __invoke(
        Request $request,
        Event $event,
        Sheet $sheet,
        AdminDomain $adminDomain
    ): RedirectResponse {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $sheet->getEvent()
            || !$this->csrfTokenManager->isTokenValid(new CsrfToken(IntentionType::INTENTION_CANCEL_ORDERS, $request->get('_token')))
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $this->csrfTokenManager->refreshToken(IntentionType::INTENTION_CANCEL_ORDERS);

        try {
            $this->commandBus->handle(new CancelAll($sheet, $adminDomain->getAdmin()));
            $this->flashBag->add('success', 'flash.admin.orders.cancelled.success');
        } catch (OrderCanNotBeCancelledException $exception) {
            $this->flashBag->add('error', 'flash.admin.orders.cancelled.error');
        }

        return new RedirectResponse(
            $this->router->generate('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
                '_fragment' => 'sheetOrders',
            ])
        );
    }
}
