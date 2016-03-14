<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Sheet\BatchValidate;
use Proximum\Vimeet\Bundle\AppBundle\Flash\TranschoiceMessage;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\FilterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $filters  = [];
        $form     = $this->createFilterForm(FilterType::class, $filters);
        $filtered = $form->handleRequest($request)->isSubmitted() && $form->isValid();

        if ($filtered) {
            $filters = $form->getData();
        }

        $sheets = $this
            ->get('query.sheet.sheet_list_view_factory')
            ->paginate($event, $filters, $request->query->getInt('page', 1), 20, $locale);

        return $this->render('VimeetAppBundle:Admin/Sheet:list.html.twig', [
            'event'    => $event,
            'sheets'   => $sheets,
            'form'     => $form->createView(),
            'filtered' => $filtered,
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
        $ids      = $request->request->get('ids', []);
        $validate = $request->request->getBoolean('validate');

        if (!empty($ids) && $validate) {
            $result = $this->get('command.sheet.batch_validate_handler')->handle(new BatchValidate($ids));

            $this->addFlash('success', new TranschoiceMessage(
                'flash.admin.sheet_batch.validate.success',
                $result->count,
                ['%count%' => $result->count]
            ));
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
