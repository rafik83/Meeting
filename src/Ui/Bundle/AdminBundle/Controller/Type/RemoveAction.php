<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\Remove;
use Proximum\Vimeet\Application\Exception\Type\TypeUsedBySheetException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param CommandBusInterface                  $commandBus
     * @param FlashBagInterface                    $flashBag
     * @param RouterInterface                      $router
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function __invoke(Event $event, Type $type): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        if ($event !== $type->getEvent()) {
            throw new NotFoundHttpException('Type not found.');
        }

        $remove = new Remove($type);
        try {
            $this->commandBus->handle($remove);
            $this->flashBag->add('success', 'flash.admin.type.remove.success');
        } catch (TypeUsedBySheetException $exception) {
            $this->flashBag->add('error', 'flash.admin.type.remove.error');
        }

        return new RedirectResponse($this->router->generate('admin_type_list', [
            'event' => $event->getId(),
        ]));
    }
}
