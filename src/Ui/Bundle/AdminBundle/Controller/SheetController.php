<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\ImportMapping;
use Proximum\Vimeet\Application\Command\Participant\UpdateVisio;
use Proximum\Vimeet\Application\Command\Sheet\AssignSpot;
use Proximum\Vimeet\Application\Command\Sheet\AssignSpotResult;
use Proximum\Vimeet\Application\Command\Sheet\Batch;
use Proximum\Vimeet\Application\Command\Sheet\BatchResult;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotActiveException;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotFoundException;
use Proximum\Vimeet\Application\Query\ConditionRules\Filters\GetFiltersByTypeAndLocaleQuery;
use Proximum\Vimeet\Application\Query\Participant\Import\ImportMappingViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedSheetListViewQuery;
use Proximum\Vimeet\Application\Query\Type\GetAllowedTypesByAdminQuery;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Application\View\Sheet\SheetListView;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
use Proximum\Vimeet\Domain\Filter\SheetFilter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Infrastructure\Adapter\QueryBus;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Filter\SheetFilterSubmittedDataGetter;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\ImportMappingType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\BatchType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\SheetFilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Proximum\Vimeet\Ui\Flash\TranschoiceMessage;
use Proximum\Vimeet\Ui\Flash\TransMessage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SheetController extends Controller
{
    private const SHEETS_PER_PAGE = 100;

    /**
     * @param Request     $request
     * @param Event       $event
     * @param AdminDomain $adminDomain
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event, AdminDomain $adminDomain)
    {
        // Access
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $selectedSheetsPage = $request->query->getInt('page', 1);

        $sheetFilter = $this->get(SheetFilter::class);
        $savedFilters = $sheetFilter->get($event);
        $admin = $adminDomain->getAdmin();

        // redirect to list with default filters if no parameters
        if (!$this->isRequestContainFilters($request) && empty($savedFilters)) {
            return $this->redirectToRoute('admin_sheet', array_merge(
                ['event' => $event->getId(), 'page' => $selectedSheetsPage],
                SheetFilterType::getDefaultFilters()
            ));
        }

        if (!$this->isRequestContainFilters($request) && null !== $savedFilters) {
            return $this->redirectToRoute('admin_sheet', array_merge(
                ['event' => $event->getId(), 'page' => $selectedSheetsPage],
                $savedFilters
            ));
        }

        if (null !== $request->query->get('reset')) {
            $sheetFilter->clear($event);
            $this->get(RuleStorageInterface::class)->removeRules($event, 'sheet');

            return $this->redirectToRoute('admin_sheet', ['event' => $event->getId()]);
        }

        $filters = SheetFilterType::getDefaultFilters();

        $sheetFilterForm = $this->createFilterForm(SheetFilterType::class, $filters, [
            'event' => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'user' => $admin,
        ]);

        $isFiltered = $sheetFilterForm->handleRequest($request)->isSubmitted() && $sheetFilterForm->isValid();

        if ($isFiltered) {
            $filters = $sheetFilterForm->getData();

            // save filter into session
            $sheetFilter->add($event, $this->getEnabledFilters(
                $sheetFilterForm,
                $request->query->all()
            ));
        }

        if ($request->query->get('rules')) {
            $this->get(RuleStorageInterface::class)->saveRules($event, 'sheet', $request->query->get('rules'));
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        // Pagination
        try {
            $conditions = $this->get(RuleStorageInterface::class)->getRules($event, $locale, 'sheet');
            $query = new PaginatedSheetListViewQuery(
                $event,
                $filters,
                $selectedSheetsPage,
                self::SHEETS_PER_PAGE, // number of sheets by page
                $locale,
                $admin,
                $conditions
            );
            /** @var PaginatedResult $sheets */
            $sheets = $this->get('tactician.commandbus.query')->handle($query);
        } catch (UnavailableCurrentPageException $ex) {
            $this->addFlash('warning', 'flash.admin.sheet.unavailablePage.warning');

            return $this->redirectToRoute('admin_sheet', ['event' => $event->getId()]);
        }

        $types = $this->get('tactician.commandbus.query')->handle(new GetAllowedTypesByAdminQuery(
            $admin,
            $event
        ));

        // Batch
        $batch = new Batch($event, $admin, $locale);
        $batchForm = $this->createForm(BatchType::class, $batch, [
            'ids' => $sheets->map(function (SheetListView $listView) {
                return $listView->id;
            }),
            'event' => $event,
            'types' => $types,
            'locale' => $request->getLocale(),
            'action' => $this->generateUrl(
                'admin_sheet_batch',
                [
                    'event' => $event->getId(),
                    'page' => $selectedSheetsPage,
                ]
            ),
        ]);

        $sheetFilterView = $sheetFilterForm->createView();
        $queryBuilderFilters = $this->get(QueryBus::class)->handle(
            new GetFiltersByTypeAndLocaleQuery($event, 'sheet', $locale)
        );

        return $this->render('AdminBundle:Sheet:list.html.twig', [
            'locale' => $request->getLocale(),
            'event' => $event,
            'typesByEvent' => $this->getTypesByEvent($types, $request->getLocale()),
            'sheets' => $sheets,
            'filters_summary' => $this->get('filter_summary')->getFilters(
                $sheetFilterView,
                $filters,
                $event,
                $request->getLocale()
            ),
            'batch_form' => $batchForm->createView(),
            'filter_form' => $sheetFilterView,
            'rules' => $this->get(RuleStorageInterface::class)->getRulesQuery($event, 'sheet'),
            'filters' => $queryBuilderFilters,
        ]);
    }

    /**
     * @param Request     $request
     * @param AdminDomain $adminDomain
     * @param Event       $event
     *
     * @return RedirectResponse
     */
    public function batchAction(Request $request, AdminDomain $adminDomain, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $admin = $adminDomain->getAdmin();

        $filters = $this->get(SheetFilterSubmittedDataGetter::class)->handle(
            $event,
            $admin,
            $request->getLocale()
        );

        $selectedSheetsPage = $request->query->getInt('page', 1);
        $batch = new Batch(
            $event,
            $admin,
            $event->getAvailableLocale($request->getLocale()),
            $filters,
            $this->get(RuleStorageInterface::class)->getRules($event, $event->getAvailableLocale($request->getLocale()), 'sheet')
        );

        $types = $this->get('tactician.commandbus.query')->handle(new GetAllowedTypesByAdminQuery(
            $admin,
            $event
        ));

        $batchForm = $this->createForm(BatchType::class, $batch, [
            'ids' => $this->get('vimeet_infrastructure.repository.sheet_repository')->getIdsByEvent($event),
            'event' => $event,
            'types' => $types,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'action' => $this->generateUrl('admin_sheet_batch', ['event' => $event->getId()]),
        ]);

        if ($batchForm->handleRequest($request)->isSubmitted()) {
            if ($batchForm->isValid()) {
                $batch->assign = $batchForm->get('assign')->isClicked();
                $batch->accept = $batchForm->get('accept')->isClicked();
                $batch->pending = $batchForm->get('pending')->isClicked();
                $batch->validate = $batchForm->get('validate')->isClicked();
                $batch->draft = $batchForm->get('validationStateDraft')->isClicked();
                $batch->validationValidate = $batchForm->get('validationStateValidate')->isClicked();

                $batch->duplicate = $batchForm->has('duplicate')
                    ? $batchForm->get('duplicate')->isClicked()
                    : false;

                if ($this->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
                    $batch->refuse = $batchForm->get('refuse')->isClicked();
                    $batch->printPdf  = $batchForm->get('printPdf')->isClicked();

                    if ($batchForm->get('printPlanning')->isClicked()) {
                        $batch->printOption = Batch::PRINT_OPTION_PLANNING;
                    } elseif ($batchForm->get('printPlanningAndBadge')->isClicked()) {
                        $batch->printOption = Batch::PRINT_OPTION_PLANNING_AND_BADGE;
                    } elseif ($batchForm->get('printBadge')->isClicked()) {
                        $batch->printOption = Batch::PRINT_OPTION_BADGE;
                    }
                }

                if ($this->isGranted('ROLE_ALLOWED_TO_ADMIN')) {
                    $batch->enable = $batchForm->get('enable')->isClicked();
                    $batch->disable = $batchForm->get('disable')->isClicked();
                    $batch->addCatalog = $batchForm->get('addCatalog')->isClicked();
                    $batch->removeCatalog = $batchForm->get('removeCatalog')->isClicked();
                    $batch->assignToGroup = $batchForm->get('assignToGroup')->isClicked();

                    if ($batchForm->has('generateInvoice')) {
                        $batch->generateInvoice = $batchForm->get('generateInvoice')->isClicked();
                    }

                    if ($batchForm->has('printInvoices')) {
                        $batch->printInvoices = $batchForm->get('printInvoices')->isClicked();
                    }
                }

                /** @var BatchResult $result */
                $result = $this->get('tactician.commandbus')->handle($batch);

                if (empty($result->ignoredSheetsMessage)) {
                    $this->addFlash('success', new TranschoiceMessage($result->message, $result->count, [
                        '%count%' => $result->count,
                    ]));
                } else {
                    $this->addFlash('warning', new TransMessage($result->message, [
                        '%sheets%' => $result->ignoredSheetsMessage,
                    ]));
                }
            } else {
                $this->addFlash('error', (string) $batchForm->getErrors(true));
            }
        }

        return $this->redirectToRoute('admin_sheet', ['event' => $event->getId(), 'page' => $selectedSheetsPage]);
    }

    /**
     * @param string $type
     * @param array  $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method' => 'GET',
            'csrf_protection' => false,
            'required' => false,
            'allow_extra_fields' => true,
        ]));
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return JsonResponse
     */
    public function assignSpotAction(Request $request, Event $event, Sheet $sheet)
    {
        $this->checkAccess($event);

        $data = json_decode($request->getContent(), true);

        try {
            $command = new AssignSpot($event, $sheet, $data['value']);
            /** @var AssignSpotResult $result */
            $result = $this->get('tactician.commandbus')->handle($command);
        } catch (SpotNotFoundException $exception) {
            return new JsonResponse([
                'error' => $this->get('translator')->trans('admin.sheet.assign.spot.notFound'),
            ], 404);
        } catch (SpotNotActiveException $exception) {
            return new JsonResponse([
                'error' => $this->get('translator')->trans('admin.sheet.assign.spot.notActive'),
            ], 404);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'error' => $this->get('translator')->trans('admin.sheet.assign.spot.exception'),
            ], 500);
        }

        if ($result->hasInfo()) {
            $infos = [
                'info' => $this->get('translator')->trans(
                    'admin.sheet.assign.spot.numberOfSheet', ['%count%' => $result->getSheetNumber()]
                ),
            ];
        } else {
            $infos = [];
        }

        return new JsonResponse(
            array_merge([
                'value' => $command->spotCode,
            ], $infos)
        );
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     *
     * @return Response
     */
    public function importMappingAction(Request $request, Event $event, Type $type)
    {
        $this->checkAccess($event);
        $this->denyAccessUnlessGranted('PERMISSION_PARTICIPANT_IMPORT_ACCESS');

        $locale = $event->getAvailableLocale($request->getLocale());

        $importMappingViewQuery = new ImportMappingViewQuery($type, $locale);

        try {
            /** @var ImportMappingView $importMappingView */
            $importMappingView = $this->get('tactician.commandbus.query')->handle($importMappingViewQuery);
        } catch (\Exception $exception) {
            $this->addFlash('error', 'flash.admin.sheet.participant.import.error');

            return $this->redirectToRoute('admin_sheet_import', ['event' => $event->getId()]);
        }

        $importMapping = new ImportMapping(
            $event,
            $type,
            $this->getUser(),
            $locale,
            $importMappingView
        );

        $form = $this->createForm(ImportMappingType::class, $importMapping, [
            'locale' => $locale,
            'importMappingView' => $importMappingView,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($importMapping);

            return $this->redirectToRoute('admin_sheet_import_result', [
                'event' => $event->getId(),
                'type' => $type->getId(),
            ]);
        }

        return $this->render('AdminBundle:Sheet:importMapping.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function importResultAction(Event $event)
    {
        $this->checkAccess($event);

        $participantDenormalizerView = $this->get('query.participant.import.import_result_view_query_handler')
            ->handle();

        return $this->render('AdminBundle:Sheet:importResult.html.twig', [
            'event' => $event,
            'view' => $participantDenormalizerView,
        ]);
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param Participant $participant
     *
     * @return JsonResponse
     */
    public function updateVisioAction(Request $request, Event $event, Participant $participant)
    {
        $this->checkAccess($event);

        if (true === $event->getConfiguration()->isVisio()) {
            throw new AccessDeniedException();
        }

        $visioParam = $request->request->get('isVisio');

        if ('true' !== $visioParam && 'false' !== $visioParam) {
            return new JsonResponse([
                'error' => $this->get('translator')->trans('admin.sheet.participant.invalid-parameters'),
            ], 404);
        }

        $isVisio = 'true' !== $visioParam ? false : true;

        if ($participant->getSheet()->getEvent() !== $event) {
            return new JsonResponse([
                'error' => $this->get('translator')->trans('admin.sheet.participant.not_found'),
            ], 404);
        }

        $command = new UpdateVisio($participant, $isVisio);

        $this->get('tactician.commandbus')->handle($command);

        return new JsonResponse([
            'message' => $this->get('translator')->trans('admin.sheet.participant_visio.success'),
        ], 200);
    }

    private function getTypesByEvent(array $types, string $requestLocale): array
    {
        $typesByEvent = [];
        $i = 0;

        /** @var Type $type */
        foreach ($types as $type) {
            $event = $type->getEvent();

            // Start from 0 to keep sorting while parsing array in javascript
            if (isset($typesByEvent[$i]['id']) && $typesByEvent[$i]['id'] !== $event->getId()) {
                ++$i;
            }

            $typesByEvent[$i]['id'] = $event->getId();
            $typesByEvent[$i]['title'] = $event->getTitle();
            $typesByEvent[$i]['types'][] = [
                'id' => $type->getId(),
                'title' => $type->getTitle($event->getAvailableLocale($requestLocale)),
            ];
        }

        return $typesByEvent;
    }

    /**
     * @param FormInterface $filterFullForm
     * @param array         $filters
     *
     * @return array
     */
    private function getEnabledFilters(FormInterface $filterFullForm, array $filters)
    {
        $enabledFilters = array_map(function (FormInterface $child) {
            return $child->getName();
        }, $filterFullForm->all());

        foreach ($filters as $key => $filter) {
            if (!in_array($key, $enabledFilters)) {
                unset($filters[$key]);
            }
        }

        return $filters;
    }

    /**
     * @param Event $event
     */
    private function checkAccess(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');
    }

    /**
     * Check if request contains Sheet filters. Exclude page query parameter
     *
     * @param Request $request
     *
     * @return bool
     */
    private function isRequestContainFilters(Request $request)
    {
        if (empty($request->query->all())) {
            return false;
        }

        if (1 === \count($request->query->all()) && !empty($request->query->get('page'))) {
            return false;
        }

        return true;
    }
}
