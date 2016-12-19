<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Messaging;

use Proximum\Vimeet\Application\Query\Messaging\Campaign\ListViewQuery;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\TargetFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CampaignController extends Controller
{
    /**
     * First step of a messaging campaign creation: display the list of active sheets for the current event
     * and enable the user to select some criteria to filter that list (campaign's "targets").
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return Response|RedirectResponse
     */
    public function selectSheetsAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $locale = $event->getAvailableLocale($request->getLocale());

        $filterForm = $this->createForm(TargetFilterType::class, [], [
            'event'           => $event,
            'locale'          => $locale,
            'user'            => $this->getUser(),
            'method'          => 'get',
            'csrf_protection' => false,
        ])->add('submit', SubmitType::class, [
            'label' => 'form.sheet_comment.children.filter.label',
            'attr'  => ['class' => 'btn btn-default'],
        ]);

        $filterForm->handleRequest($request);
        $filters = $filterForm->getData();

        if ('POST' == $request->getMethod()) {
            $this->addFlash('success', 'admin.messaging.campaign.sheet_list.success');

            return $this->redirectToRoute('admin_messaging_campaign_select_sheets', ['event' => $event->getId()]);
        }

        $query          = new SheetListViewQuery($event, $filters, $locale);
        $sheets         = $this->get('tactician.commandbus.query')->handle($query);
        $filterFormView = $filterForm->createView();

        return $this->render('AdminBundle:Messaging\Campaign:select_sheets.html.twig', [
            'event'           => $event,
            'sheets'          => $sheets,
            'filter_form'     => $filterFormView,
            'filters_summary' => $this->get('filter_summary')->getFilters($filterFormView, $filters, $locale),
        ]);
    }

    /**
     * Display all messaging campaigns for a given event.
     *
     * @param Request $request
     * @param Event $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN', $event);

        $query     = new ListViewQuery($event);
        $campaigns = $this->get('tactician.commandbus')->handle($query);

        return $this->render('AdminBundle:Messaging\Campaign:list.html.twig', [
            'event'     => $event,
            'campaigns' => $campaigns,
        ]);
    }
}
