<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\WhatType;
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
            return $this->redirectToRoute('admin_who_see_who_dont_see_what', [
                'id'          => $event->getId(),
                'seerType'    => $form->get('seer')->getData()->getIdentifier(),
                'seerId'      => $form->get('seer')->getData()->getId(),
                'seeableType' => $form->get('seeable')->getData()->getIdentifier(),
                'seeableId'   => $form->get('seeable')->getData()->getId(),
            ]);
        }

        $sees = $this->get('vimeet_infrastructure.repository.see_repository')->getByEvent($event);

        return $this->render('VimeetAppBundle:Admin/See:list.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
            'sees'  => $sees,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param string  $seerType
     * @param int     $seerId
     * @param string  $seeableType
     * @param int     $seeableId
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, $seerType, $seerId, $seeableType, $seeableId)
    {
        $seer    = $this->getDoctrine()->getRepository(sprintf('Entity:%s', ucfirst($seerType)))->find($seerId);
        $seeable = $this->getDoctrine()->getRepository(sprintf('Entity:%s', ucfirst($seeableType)))->find($seeableId);

        $form = $this->createForm(new WhatType(), [], [
            'method' => 'POST',
            'who'    => $seeable,
            'locale' => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

            $see = new See($event, $seer, $seeable, $form->getData());
            $this->getDoctrine()->getManager()->persist($see);
            $this->getDoctrine()->getManager()->flush($see);

            return $this->redirectToRoute('admin_see_list', ['id' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/See:update.html.twig', [
            'form'    => $form->createView(),
            'event'   => $event,
            'seer'    => $seer,
            'seeable' => $seeable,
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
