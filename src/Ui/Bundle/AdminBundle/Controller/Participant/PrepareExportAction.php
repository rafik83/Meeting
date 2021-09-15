<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Participant;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Participant\Export\PrepareExport;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
use Proximum\Vimeet\Domain\Exception\Participant\Export\NoParticipantToExportException;
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

    /** @var SheetFilterSubmittedDataGetter */
    private $sheetFilterSubmittedDataGetter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RuleStorageInterface */
    private $ruleStorage;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        SheetFilterSubmittedDataGetter $sheetFilterSubmittedDataGetter,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        FlashBagInterface $flashBag,
        RuleStorageInterface $ruleStorage
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->sheetFilterSubmittedDataGetter = $sheetFilterSubmittedDataGetter;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->flashBag = $flashBag;
        $this->ruleStorage = $ruleStorage;
    }

    /**
     * CSV export of participant's filtered sheets. Requires super admin or organizer role.
     *
     * @param AdminDomain $adminDomain
     * @param Request     $request
     * @param Event       $event
     *
     * @return RedirectResponse
     */
    public function __invoke(AdminDomain $adminDomain, Request $request, Event $event): RedirectResponse
    {
        if (false === $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || false ===  $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $locale = $event->getAvailableLocale($request->getLocale());
        $exportCommand = new PrepareExport(
            $event,
            $this->sheetFilterSubmittedDataGetter->handle($event, $adminDomain->getAdmin(), $locale),
            $adminDomain->getAdmin(),
            $locale,
            $this->ruleStorage->getRules($event, $locale, 'sheet')
        );

        try {
            $this->commandBus->handle($exportCommand);

            $this->flashBag->add('success', 'flash.admin.participant.export.planned');
        } catch (NoParticipantToExportException $noParticipantToExportException) {
            $this->flashBag->add('error', 'flash.admin.participant.export.error.noParticipantToExport');
        }

        return new RedirectResponse($this->router->generate('admin_sheet', [
            'event' => $event->getId(),
        ]));
    }
}
