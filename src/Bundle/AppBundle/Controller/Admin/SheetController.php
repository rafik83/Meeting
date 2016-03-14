<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Sheet\Batch;
use Proximum\Vimeet\Application\Command\Sheet\BatchAssign;
use Proximum\Vimeet\Application\Command\Sheet\BatchValidate;
use Proximum\Vimeet\Application\Query\Sheet\SheetListView;
use Proximum\Vimeet\Bundle\AppBundle\Flash\TranschoiceMessage;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\FilterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\BatchType;

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

        // Locale
        $locale = $event->getAvailableLocale($request->getLocale());

        // Filters
        $filters    = [];
        $filterForm = $this->createFilterForm(FilterType::class, $filters, ['event' => $event, 'locale' => $request->getLocale()]);
        $filtered   = $filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid();

        if ($filtered) {
            $filters = $filterForm->getData();
        }

        // Pagination
        $sheets = $this
            ->get('query.sheet.sheet_list_view_factory')
            ->paginate($event, $filters, $request->query->getInt('page', 1), 20, $locale);

        // Batch
        $batch     = new Batch();
        $batchForm = $this->createForm(BatchType::class, $batch, [
            'ids'    => $sheets->map(function (SheetListView $listView) { return $listView->id; }),
            'event'  => $event,
            'action' => $this->generateUrl('admin_sheet_batch', ['event' => $event->getId()]),
        ]);

        return $this->render('VimeetAppBundle:Admin/Sheet:list.html.twig', [
            'event'       => $event,
            'sheets'      => $sheets,
            'filter_form' => $filterForm->createView(),
            'filtered'    => $filtered,
            'batch_form'  => $batchForm->createView(),
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
        $batch     = new Batch();
        $batchForm = $this->createForm(BatchType::class, $batch, [
            'ids'    => $this->get('vimeet_infrastructure.repository.sheet_repository')->getIdsByEvent($event),
            'event'  => $event,
            'action' => $this->generateUrl('admin_sheet_batch', ['event' => $event->getId()]),
        ]);

        if ($batchForm->handleRequest($request)->isSubmitted()) {
            if ($batchForm->isValid()) {
                $batch->validate = $batchForm->get('validate')->isClicked();
                $batch->assign   = $batchForm->get('assign')->isClicked();

                $result = $this->get('command.sheet.batch_handler')->handle($batch);

                if ($batch->validate) {
                    $this->addFlash('success', new TranschoiceMessage('flash.admin.sheet_batch.validate.success', $result->count, ['%count%' => $result->count]));
                } elseif ($batch->assign) {
                    $this->addFlash('success', new TranschoiceMessage('flash.admin.sheet_batch.assign.success', $result->count, ['%count%' => $result->count, '%name%' => $batch->follower->getDisplayName()]));
                }
            } else {
                $this->addFlash('error', (string) $batchForm->getErrors(true));
            }
        }

        return $this->redirectToRoute('admin_sheet', ['event' => $event->getId()]);
    }

    /**
     * @param string $type
     * @param string $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method'          => 'GET',
            'csrf_protection' => false,
            'required'        => false,
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

        $details = $this->get('sheet.sheet_details_view_factory')->create($sheet, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Sheet:details.html.twig', [
            'event'   => $event,
            'sheet'   => $sheet,
            'details' => $details,
        ]);
    }
}
