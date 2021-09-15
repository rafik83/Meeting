<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet\Export;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\Export\PrepareExport;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter\SheetFilterSubmittedDataGetter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PrepareExportAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var SheetFilterSubmittedDataGetter */
    private $sheetFilterSubmittedDataGetter;

    /** @var RuleStorageInterface */
    private $ruleStorage;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        SheetFilterSubmittedDataGetter $sheetFilterSubmittedDataGetter,
        RuleStorageInterface $ruleStorage,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->sheetFilterSubmittedDataGetter = $sheetFilterSubmittedDataGetter;
        $this->ruleStorage = $ruleStorage;
        $this->router = $router;
    }

    /**
     * CSV export of event's filtered sheets. Requires super admin or organizer role.
     *
     * @param AdminDomain $adminDomain
     * @param Request     $request
     * @param Event       $event
     *
     * @return RedirectResponse
     */
    public function __invoke(AdminDomain $adminDomain, Request $request, Event $event): RedirectResponse
    {
        // Only super admin & organizers are allowed to export sheets:
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $admin = $adminDomain->getAdmin();

        $displayNomenclatureIds = $request->query->getBoolean('displayNomenclatureIds');
        $locale = $event->getAvailableLocale($request->getLocale());
        $prepareExport = new PrepareExport(
            $event,
            $this->getFilters($event, $adminDomain->getAdmin(), $locale),
            $locale,
            $admin,
            $displayNomenclatureIds,
            $this->ruleStorage->getRules($event, $locale, 'sheet')
        );

        $this->commandBus->handle($prepareExport);

        $this->flashBag->add('success', 'flash.admin.sheet.export.prepared');

        return new RedirectResponse(
            $this->router->generate('admin_sheet', ['event' => $event->getId()])
        );
    }

    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     *
     * @return mixed
     */
    private function getFilters(Event $event, Admin $admin, string $locale)
    {
        return $this->sheetFilterSubmittedDataGetter->handle($event, $admin, $locale);
    }
}
