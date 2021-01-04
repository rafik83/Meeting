<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveAction
{
    /** @var CommandBus */
    private $commandBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /**
     * @param CommandBus                           $commandBus
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param FlashBagInterface                    $flashBag
     * @param RouterInterface                      $router
     */
    public function __construct(
        CommandBus $commandBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    /**
     * @param Event $event
     * @param Tip   $tip
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function __invoke(Event $event, Tip $tip): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $tip->getEvent() !== $event
        ) {
            throw new AccessDeniedException('You can not remove this tip as it is not on this event');
        }

        $this->commandBus->handle(new Remove($tip));
        $this->flashBag->add('success', 'flash.admin.tip.remove.success');

        return new RedirectResponse($this->router->generate('admin_tip_event_list', [
            'event' => $event->getId(),
        ]));
    }
}
