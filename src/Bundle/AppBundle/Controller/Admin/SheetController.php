<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Sheet\AddComment;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\CommentType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\FilterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $locale   = $event->getAvailableLocale($request->getLocale());

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

        $addComment = new AddComment($sheet, $this->getUser(), new \DateTime());

        $form = $this->createForm(CommentType::class, $addComment, [
            'action' => $this->generateUrl('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ]),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.sheet.add_comment_handler')->handle($addComment);
            $this->addFlash('success', 'flash.admin.sheet.add_comment.success');

            return $this->redirectToRoute('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/Sheet:details.html.twig', [
            'event'   => $event,
            'sheet'   => $sheet,
            'details' => $details,
            'form'    => $form->createView(),
        ]);
    }
}
