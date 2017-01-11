<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Messaging;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\Create;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectMessage;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectRecipients;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\Send;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\ListViewQuery;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListView;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\CreateCampaignType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\SelectMessageType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\SelectRecipientsType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\TargetFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;

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

        $query          = new SheetListViewQuery($event, $filters, $locale);
        $sheets         = $this->get('tactician.commandbus.query')->handle($query);
        $filterFormView = $filterForm->createView();

        $createCampaignForm = $this->createForm(CreateCampaignType::class, new Create($event, $request->get($filterForm->getName(), [])), [
            'sheet_ids' => array_map(function (SheetListView $sheet) {
                return $sheet->id;
            }, $sheets),
            'action'    => $this->generateUrl('admin_messaging_campaign_select_sheets', ['event' => $event->getId()]) . '?' . $request->getQueryString(),
        ]);

        if ('POST' == $request->getMethod()) {
            $createCampaignForm->handleRequest($request);

            if ($createCampaignForm->isValid()) {
                $campaign = $this->get('tactician.commandbus')->handle($createCampaignForm->getData());
                $this->addFlash('success', 'flash.admin.messaging.campaign.create.success');

                return $this->redirectToRoute('admin_messaging_campaign_select_recipients', [
                    'event'    => $event->getId(),
                    'campaign' => $campaign->getId(),
                ]);
            }
        }

        return $this->render('AdminBundle:Messaging\Campaign:select_sheets.html.twig', [
            'event'                => $event,
            'sheets'               => $sheets,
            'filter_form'          => $filterFormView,
            'filters_summary'      => $this->get('filter_summary')->getFilters($filterFormView, $filters, $locale),
            'create_campaign_form' => $createCampaignForm->createView(),
        ]);
    }

    /**
     * Second step of a messaging campaign creation: select recipients (sheet owners, participants and/or billing contacts).
     *
     * @param Request  $request
     * @param Event    $event
     * @param Campaign $campaign
     *
     * @return Response|RedirectResponse
     */
    public function selectRecipientsAction(Request $request, Event $event, Campaign $campaign)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $form = $this->createForm(SelectRecipientsType::class, new SelectRecipients($campaign));
        $form->add('submit', SubmitType::class, [
            'label' => 'form.sheet.messaging_campaign.recipients.submit.label',
            'attr'  => ['class' => 'btn btn-primary'],
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $this->get('tactician.commandbus')->handle($form->getData());
            $this->addFlash('success', 'flash.admin.messaging.campaign.recipients.success');

            return $this->redirectToRoute('admin_messaging_campaign_select_message', [
                'event'    => $event->getId(),
                'campaign' => $campaign->getId(),
            ]);
        }

        $campaignView = $this->get('assembler.messaging_campaign')->assemble($campaign);

        return $this->render('AdminBundle:Messaging\Campaign:select_recipients.html.twig', [
            'event'    => $event,
            'form'     => $form->createView(),
            'campaign' => $campaignView,
        ]);
    }

    /**
     * Third step of messaging campaign creation: select message.
     *
     * @param Request  $request
     * @param Event    $event
     * @param Campaign $campaign
     *
     * @return Response|RedirectResponse
     */
    public function selectMessageAction(Request $request, Event $event, Campaign $campaign)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $selectMessage = new SelectMessage($campaign);
        $form = $this->createForm(SelectMessageType::class, $selectMessage, [
            'event' => $event,
        ]);
        $form->add('submit', SubmitType::class, [
            'label' => 'form.sheet.messaging_campaign.message.submit.label',
            'attr'  => ['class' => 'btn btn-primary'],
        ]);

        if ($form->handleRequest($request)->isValid()) {
            $this->get('tactician.commandbus')->handle($selectMessage);
            $this->addFlash('success', 'flash.admin.messaging.campaign.message.success');

            return $this->redirectToRoute('admin_messaging_campaign_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Messaging\Campaign:select_message.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * Display all messaging campaigns for a given event.
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $query     = new ListViewQuery($event);
        $campaigns = $this->get('tactician.commandbus')->handle($query);

        return $this->render('AdminBundle:Messaging\Campaign:list.html.twig', [
            'event'     => $event,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Sends a given messaging Campaign.
     *
     * @param Event    $event
     * @param Campaign $campaign
     *
     * @return RedirectResponse
     */
    public function sendAction(Request $request, Event $event, Campaign $campaign)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $token = $request->request->get('_token');
        $token = $token ? new CsrfToken('send_campaign', $token) : null;

        if (!$token || !$this->get('security.csrf.token_manager')->isTokenValid($token)) {
            $this->addFlash('error', 'flash.messaging.campaign.send.failure');

            return $this->redirectToRoute('admin_messaging_campaign_list', [
                'event' => $event->getId(),
            ]);
        }

        $this->get('tactician.commandbus')->handle(new Send($campaign));
        $this->addFlash('success', 'flash.messaging.campaign.send.success');

        return $this->redirectToRoute('admin_messaging_campaign_list', [
            'event' => $event->getId(),
        ]);
    }
}
