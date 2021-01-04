<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Spot;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Query\HasEventReferenceQuery;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\ScheduleExport;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\ScheduleExportHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScheduleExportAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var ScheduleExportHandler */
    private $scheduleExportHandler;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        ScheduleExportHandler $scheduleExportHandler,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->scheduleExportHandler = $scheduleExportHandler;
        $this->queryBus = $queryBus;
    }

    /**
     * @param Event       $event
     * @param AdminDomain $adminDomain
     *
     * @return RedirectResponse
     */
    public function __invoke(Event $event, AdminDomain $adminDomain): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || false === $this->queryBus->handle(new HasEventReferenceQuery($event))
        ) {
            throw new AccessDeniedException('Access denied to the spots export');
        }

        $this->scheduleExportHandler->handle(new ScheduleExport($event, $adminDomain->getAdmin()));
        $this->flashBag->add('success', 'flash.spot.export.scheduled');

        return new RedirectResponse($this->router->generate('admin_spot_list', ['event' => $event->getId()]));
    }
}
