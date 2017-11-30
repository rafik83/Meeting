<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip;

use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Application\Query\Tip\Event\PreviewTipViewQuery;
use Proximum\Vimeet\Application\Query\Tip\Event\PaginatedTipViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\AffectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class TipEventController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $tipListViewQuery = new PaginatedTipViewQuery($event, $request->query->get('page', 1));
        $tipListView      = $this->get('tactician.commandbus')->handle($tipListViewQuery);

        return $this->render('AdminBundle:Tip:Event/list.html.twig', [
            'event'       => $event,
            'tipListView' => $tipListView,
            'locale'      => $event->getAvailableLocale($request->getLocale()),
        ]);
    }

    /**
     * @param Request       $request
     * @param Event         $event
     * @param UserInterface $admin
     *
     * @return Response
     */
    public function affectAction(Request $request, Event $event, UserInterface $admin)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());
        $affect = new Affect($event);
        $form   = $this->createForm(AffectType::class, $affect, [
            'admin'  => $admin,
            'event'  => $event,
            'locale' => $locale,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($affect);
            $this->addFlash('success', 'flash.admin.tip.affect.success');

            return $this->redirectToRoute('admin_tip_event_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Tip:Event/affect.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param Event $event
     * @param Tip   $tip
     *
     * @return RedirectResponse
     */
    public function removeAction(Event $event, Tip $tip)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($tip->getEvent() !== $event) {
            throw $this->createAccessDeniedException('You can not remove this tip as it is not on this event');
        }

        $this->get('tactician.commandbus')->handle(new Remove($tip));
        $this->addFlash('success', 'flash.admin.tip.remove.success');

        return $this->redirectToRoute('admin_tip_event_list', [
            'event' => $event->getId(),
        ]);
    }

    /**
     * @param Tip    $tip
     * @param string $locale
     *
     * @return JsonResponse
     */
    public function previewAction(Tip $tip, $locale)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($tip->hasEvent()) {
            throw $this->createAccessDeniedException('The tip has an event and can not be previewed');
        }

        $tipView = $this->get('tactician.commandbus')->handle(new PreviewTipViewQuery($tip, $locale));

        return new JsonResponse($tipView);
    }
}
