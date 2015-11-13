<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\WhoSeeWhatType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\WhoSeeWhoType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\See;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SeeController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request, Event $event)
    {
        $form = $this->createForm(new WhoSeeWhoType(), [], [
            'action' => $this->generateUrl('admin_see_list', ['id' => $event->getId()]),
            'method' => 'POST',
            'event'  => $event,
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $see = new See($event, $form->get('seer')->getData(), $form->get('seeable')->getData());
            $this->get('vimeet_infrastructure.repository.see_repository')->add($see);

            return $this->redirectToRoute('admin_see_list', ['id' => $event->getId()]);
        }

        $sees = $this->get('vimeet_infrastructure.repository.see_repository')->getByEvent($event);

        return $this->render('VimeetAppBundle:Admin/See:list.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
            'sees'  => $sees,
        ]);
    }

    /**
     * @ParamConverter(
     *   "see",
     *   class="Proximum\Vimeet\Domain\Model\See",
     *   options={"id" = "see_id"}
     * )
     *
     * @param Request $request
     * @param Event   $event
     * @param See     $see
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, See $see)
    {
        $form = $this->createForm(new WhoSeeWhatType(), [], [
            'action'  => $this->generateUrl('admin_see_update', ['id' => $event->getId(), 'see_id' => $see->getId()]),
            'method'  => 'POST',
            'event'   => $event,
            'seeable' => $see->getSeeableType() ? : $see->getSeeableCategory(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

            return $this->redirectToRoute('admin_see_update', ['id' => $event->getId(), 'see_id' => $see->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/See:update.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
            'see'   => $see,
        ]);
    }

    /**
     * @ParamConverter(
     *   "see",
     *   class="Proximum\Vimeet\Domain\Model\See",
     *   options={"id" = "see_id"}
     * )
     *
     * @param Event $event
     * @param See   $see
     *
     * @return RedirectResponse
     */
    public function deleteAction(Event $event, See $see)
    {
        if ($see->getEvent() !== $event) {
            throw $this->createNotFoundException('See not found');
        }

        $this->get('vimeet_infrastructure.repository.see_repository')->remove($see);

        return $this->redirectToRoute('admin_see_list', ['id' => $event->getId()]);
    }
}
