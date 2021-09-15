<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User\Event;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\User\Event\ScanEventEntranceCommand;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CheckinEventEntranceAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        \DateTimeInterface $dateTime,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->dateTime = $dateTime;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Event $event, User $user): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$event->isAccessControlEnabled()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        try {
            $this->commandBus->handle(
                new ScanEventEntranceCommand(
                    $event,
                    $user,
                    $this->dateTime
                )
            );

            $this->flashBag->add('success', 'flash.admin.checkin.success');
        } catch (\Exception $e) {
            $this->flashBag->add('error', 'flash.admin.checkin.error');
        }

        return new RedirectResponse(
            $this->router->generate(
                'admin_event_fast_checkin_actions',
                ['event' => $event->getId(), 'user' => $user->getId(),]
            )
        );
    }
}
