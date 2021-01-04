<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Sheet\GetSheetIdsByFiltersQuery;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter\SheetFilterSubmittedDataGetter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportUploadedObjectsBySheetsAction
{
    /** @var SheetFilterSubmittedDataGetter */
    private $sheetFilterSubmittedDataGetter;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var QueryBusInterface */
    private $queryBus;

    /** var \DateTimeInterface */
    private $datetime;

    /** @var RuleStorageInterface */
    private $ruleStorage;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        SheetFilterSubmittedDataGetter $sheetFilterSubmittedDataGetter,
        ExtraDataRepositoryInterface $extraDataRepository,
        UrlGeneratorInterface $urlGenerator,
        FlashBagInterface $flashBag,
        JobQueueInterface $jobQueue,
        QueryBusInterface $queryBus,
        \DateTimeInterface $datetime,
        RuleStorageInterface $ruleStorage
    ) {
        $this->sheetFilterSubmittedDataGetter = $sheetFilterSubmittedDataGetter;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->extraDataRepository = $extraDataRepository;
        $this->urlGenerator = $urlGenerator;
        $this->flashBag = $flashBag;
        $this->jobQueue = $jobQueue;
        $this->queryBus = $queryBus;
        $this->datetime = $datetime;
        $this->ruleStorage = $ruleStorage;
    }

    public function __invoke(Request $request, Event $event, AdminDomain $adminDomain): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE') ||
            !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException();
        }

        $url = $this->urlGenerator->generate('admin_sheet', ['event' => $event->getId()]);
        $locale = $event->getAvailableLocale($request->getLocale());
        $filters = $this->sheetFilterSubmittedDataGetter->handle($event, $adminDomain->getAdmin(), $locale);

        /** @var SheetIdsView $sheetIdsView */
        $sheetIdsView = $this->queryBus->handle(
            new GetSheetIdsByFiltersQuery(
                $event,
                $filters,
                $locale,
                $this->ruleStorage->getRules($event, $locale, 'sheet')
            )
        );

        if (empty($sheetIdsView->sheetIds)) {
            $this->flashBag->add('error', 'flash.admin.event.export.uploaded_objects.error');

            return new RedirectResponse($url);
        }

        $extraData = new ExtraData(
            $event,
            Type::ADMIN_SHEET_BATCH_IDS,
            implode(', ', $sheetIdsView->sheetIds),
            $this->datetime
        );

        $this->extraDataRepository->add($extraData);
        $this->jobQueue->exportUploadedObjectsBySheets($event, $adminDomain->getAdmin(), $extraData);
        $this->flashBag->add('success', 'flash.admin.event.export.uploaded_objects.success');

        return new RedirectResponse($url);
    }
}
