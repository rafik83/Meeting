<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Order;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Order\RemovePromotionCode;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RemovePromotionCodeAction
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
        AdminDomain $adminDomain,
        Event $event,
        Order $order,
        Order\PromotionCode $promotionCode
    ): RedirectResponse {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $order->hasInvoice()
            || $event !== $order->getSheet()->getEvent()
            || !in_array($promotionCode, $order->getPromotionCodes(), true)
            || !$this->csrfTokenManager->isTokenValid(
                new CsrfToken(IntentionType::INTENTION_REMOVE_PROMOTION_CODE_FROM_ORDER, $request->get('_token'))
            )
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $this->csrfTokenManager->refreshToken(IntentionType::INTENTION_REMOVE_PROMOTION_CODE_FROM_ORDER);
        $this->commandBus->handle(new RemovePromotionCode($promotionCode, $order));
        $this->flashBag->add('success', 'flash.admin.order.promotionCode.removed');

        return new RedirectResponse(
            $this->router->generate(
                'admin_sheet_order_edit',
                [
                    'event' => $event->getId(),
                    'order' => $order->getId(),
                ]
            )
        );
    }
}
