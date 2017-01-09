<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Import;
use Proximum\Vimeet\Application\Command\Sheet\AddComment;
use Proximum\Vimeet\Application\Command\Sheet\AssignSpot;
use Proximum\Vimeet\Application\Command\Sheet\AssignSpotResult;
use Proximum\Vimeet\Application\Command\Sheet\Batch;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotActiveException;
use Proximum\Vimeet\Application\Exception\Spot\SpotNotFoundException;
use Proximum\Vimeet\Application\Nomenclature\Charset;
use Proximum\Vimeet\Application\Query\Sheet\PaginatedSheetListViewQuery;
use Proximum\Vimeet\Application\View\Sheet\SheetListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\ImportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\BatchType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\ChangeTypeType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\CommentType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\FilterFullType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\FilterPartType;
use Proximum\Vimeet\Ui\Flash\TranschoiceMessage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class SheetController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        // Access
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        // redirect to list with default filters if no parameters
        if (empty($request->query->all()) && empty($this->get('filter.sheet_filter')->get())) {
            return $this->redirectToRoute('admin_sheet', array_merge(
                ['event' => $event->getId()],
                FilterFullType::getDefaultFilters()
            ));
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        if (empty($request->query->all()) && $this->get('filter.sheet_filter')->get() !== null) {
            return $this->redirectToRoute('admin_sheet', array_merge(
                ['event' => $event->getId()],
                $this->get('filter.sheet_filter')->get()
            ));
        }

        if ($request->query->get('reset') !== null) {
            $this->get('filter.sheet_filter')->clear();

            return $this->redirectToRoute('admin_sheet', ['event' => $event->getId()]);
        }

        $filters = FilterFullType::getDefaultFilters();

        $filterFullForm = $this->createFilterForm(FilterFullType::class, $filters, [
            'event'  => $event,
            'locale' => $locale,
            'user'   => $this->getUser(),
        ]);

        $filterPartForm = $this->createFilterForm(FilterPartType::class, [], [
            'event'  => $event,
            'locale' => $locale,
        ]);

        $filterPartForm->handleRequest(Request::create($request->getUri()));
        $filtered = $filterFullForm->handleRequest($request)->isSubmitted() && $filterFullForm->isValid();

        if ($filtered) {
            $filters = $filterFullForm->getData();
            // save filter into session
            $this->get('filter.sheet_filter')->add($this->getEnabledFilters(
                $filterFullForm,
                $request->query->all()
            ));
        }

        // Pagination
        try {
            $query = new PaginatedSheetListViewQuery(
                $event,
                $filters,
                $request->query->getInt('page', 1),
                20,
                $locale,
                $this->getUser()
            );
            /** @var PaginatedResult $sheets */
            $sheets = $this->get('tactician.commandbus.query')->handle($query);
        } catch (UnavailableCurrentPageException $ex) {
            throw $this->createNotFoundException($ex->getMessage());
        }

        // Batch
        $batch     = new Batch($this->getUser(), new \DateTime());
        $batchForm = $this->createForm(BatchType::class, $batch, [
            'ids'    => $sheets->map(function (SheetListView $listView) {
                return $listView->id;
            }),
            'event'  => $event,
            'action' => $this->generateUrl('admin_sheet_batch', ['event' => $event->getId()]),
        ]);

        $filterFormView = $filterFullForm->createView();

        return $this->render('AdminBundle:Sheet:list.html.twig', [
            'event'            => $event,
            'sheets'           => $sheets,
            'filter_form'      => $filterFormView,
            'filters_summary'  => $this->get('filter_summary')->getFilters($filterFormView, $filters, $locale),
            'filtered'         => $filtered,
            'batch_form'       => $batchForm->createView(),
            'filter_part_form' => $filterPartForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse
     */
    public function batchAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $batch     = new Batch($this->getUser(), new \DateTime());
        $batchForm = $this->createForm(BatchType::class, $batch, [
            'ids'    => $this->get('vimeet_infrastructure.repository.sheet_repository')->getIdsByEvent($event),
            'event'  => $event,
            'action' => $this->generateUrl('admin_sheet_batch', ['event' => $event->getId()]),
        ]);

        if ($batchForm->handleRequest($request)->isSubmitted()) {
            if ($batchForm->isValid()) {
                $batch->assign             = $batchForm->get('assign')->isClicked();
                $batch->accept             = $batchForm->get('accept')->isClicked();
                $batch->validate           = $batchForm->get('validate')->isClicked();
                $batch->draft              = $batchForm->get('validationStateDraft')->isClicked();
                $batch->validationValidate = $batchForm->get('validationStateValidate')->isClicked();

                if ($this->isGranted('ROLE_ALLOWED_TO_ADMIN')) {
                    $batch->enable        = $batchForm->get('enable')->isClicked();
                    $batch->disable       = $batchForm->get('disable')->isClicked();
                    $batch->addCatalog    = $batchForm->get('addCatalog')->isClicked();
                    $batch->removeCatalog = $batchForm->get('removeCatalog')->isClicked();
                }

                $result = $this->get('tactician.commandbus')->handle($batch);

                $this->addFlash('success', new TranschoiceMessage($result->message, $result->count, [
                    '%count%' => $result->count,
                ]));
            } else {
                $this->addFlash('error', (string)$batchForm->getErrors(true));
            }
        }

        return $this->redirectToRoute('admin_sheet', ['event' => $event->getId()]);
    }

    /**
     * CSV export of event's sheets. Requires super admin or organizer role.
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function exportAction(Request $request, Event $event)
    {
        // Only super admin & organizers are allowed to export sheets:
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $charset       = Charset::WINDOWS_1252;
        $serializer    = $this->get('serializer');
        $exportContent = $serializer->serialize($event, 'csv', [
            'locale'  => $request->getLocale(),
            'charset' => $charset,
        ]);

        $response    = new Response($exportContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "export_event_sheets_" . date("Y_m_d_His") . ".csv"
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));

        return $response;
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
            'method'             => 'GET',
            'csrf_protection'    => false,
            'required'           => false,
            'allow_extra_fields' => true,
        ]));
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return Response
     */
    public function detailsAction(Request $request, Event $event, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('PERMISSION_SHEET_ACCESS', $sheet);

        if ($sheet->getEvent() !== $event) {
            throw $this->createNotFoundException(
                sprintf(
                    'The sheet %s is not on this event %s',
                    $sheet->getId(),
                    $event->getId()
                )
            );
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        $details = $this->get('sheet.sheet_details_view_factory')->create($sheet, $locale);

        $changeTypeForm = null;

        if ($this->get('vimeet_infrastructure.repository.type_repository')->countByEvent($event) > 1) {
            $changeType = new ChangeType($sheet, $sheet->getType(), $this->getUser(), $locale);

            $changeTypeForm = $this->createForm(ChangeTypeType::class, $changeType, [
                'event'  => $event,
                'type'   => $sheet->getType(),
                'locale' => $locale,
                'submit' => true,
            ]);

            if ($changeTypeForm->handleRequest($request)->isSubmitted() && $changeTypeForm->isValid()) {
                $this->get('tactician.commandbus')->handle($changeType);
                $this->addFlash('success', 'flash.admin.sheet.change_type.success');

                return $this->redirectToRoute('admin_sheet_details', [
                    'event' => $event->getId(),
                    'sheet' => $sheet->getId(),
                ]);
            }
        }

        $addComment = new AddComment($sheet, $this->getUser(), new \DateTime());

        $addCommentForm = $this->createForm(CommentType::class, $addComment, [
            'action' => $this->generateUrl('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ]),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($addCommentForm->handleRequest($request)->isSubmitted() && $addCommentForm->isValid()) {
            $this->get('tactician.commandbus')->handle($addComment);
            $this->addFlash('success', 'flash.admin.sheet.add_comment.success');

            return $this->redirectToRoute('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ]);
        }

        $impersonationToken = $this->get('security.impersonate')->getEncodedToken($this->getUser(), $sheet->getOwner());

        return $this->render('AdminBundle:Sheet:details.html.twig', [
            'event'              => $event,
            'sheet'              => $sheet,
            'sheetTypeTitle'     => $sheet->getType()->getTitle($locale),
            'details'            => $details,
            'addCommentForm'     => $addCommentForm->createView(),
            'changeTypeForm'     => $changeTypeForm === null ? null : $changeTypeForm->createView(),
            'impersonationToken' => $impersonationToken,
        ]);
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
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

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
     *
     * @return Response
     */
    public function importAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $import = new Import();

        $form = $this->createForm(ImportType::class, $import, [
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'user'   => $this->getUser(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($import);

            return $this->redirectToRoute('admin_sheet_import_mapping', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Sheet:import.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     */
    public function importMappingAction(Request $request, Event $event)
    {

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
}
