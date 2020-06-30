<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\Export\ScheduleExport;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportParticipantAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        CommandBusInterface $commandBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->commandBus = $commandBus;
    }

    public function __invoke(Request $request, Event $event, AdminDomain $adminDomain): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access Denied!');
        }

        try {
            $this->commandBus->handle(
                new ScheduleExport($event, $adminDomain->getAdmin(), $event->getAvailableLocale($request->getLocale()))
            );

            $this->flashBag->add('success', 'flash.admin.happening.participation.export_scheduled');
        } catch (EmptyHappeningParticipationException $exception) {
            $this->flashBag->add('error', 'flash.admin.happening.participation.empty');
        }

        return new RedirectResponse(
            $this->router->generate('admin_happening_list', ['event' => $event->getId()])
        );
    }
}
