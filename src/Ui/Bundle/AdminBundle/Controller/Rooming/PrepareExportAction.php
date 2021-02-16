<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Export\PrepareExport;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;

class PrepareExportAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, AdminDomain $adminDomain): RedirectResponse
    {
        $command = new PrepareExport($event, $adminDomain->getAdmin(), $request->getLocale());
        $this->commandBus->handle($command);
        $this->flashBag->add('success', 'flash.admin.rooming.list.export.prepare.success');

        return new RedirectResponse(
            $this->router->generate(
                'admin_event_rooming_list',
                [
                    'event' => $event->getId(),
                ]
            )
        );
    }
}
