<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\LinkedSheets;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\Delete;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DeleteAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Event $event, LinkedSheets $linkedSheets)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || $event !== $linkedSheets->getEvent()
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $this->commandBus->handle(new Delete($linkedSheets));
        $this->flashBag->add('success', 'flash.admin.linked_sheets.delete.success');

        return new RedirectResponse(
            $this->router->generate('admin_linked_sheets_list', ['event' => $event->getId()])
        );
    }
}
