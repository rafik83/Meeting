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
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\WhoSeeWhoType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\See;
use Proximum\Vimeet\Domain\Model\WhoInterface;
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
    public function whatAction(Request $request, Event $event, $seerType, $seerId, $seeableType, $seeableId)
    {
        $seer    = $this->findWho($seerType, $seerId);
        $seeable = $this->findWho($seeableType, $seeableId);

        if (!$seer) {
            throw $this->createNotFoundException('Seer not found.');
        }

        if (!$seeable) {
            throw $this->createNotFoundException('Seeable not found.');
        }

        $see  = $this->findOrCreateSee($event, $seer, $seeable);
        $form = $this->createWhatForm($see, $request->getLocale());

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

            if (!$see->getId()) {
                $this->getDoctrine()->getManager()->persist($see);
            }
            $this->getDoctrine()->getManager()->flush($see);

            return $this->redirectToRoute('admin_see_list', ['id' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/See:what.html.twig', [
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

    /**
     * @param Event        $event
     * @param WhoInterface $seer
     * @param WhoInterface $seeable
     *
     * @return See
     */
    private function findOrCreateSee(Event $event, WhoInterface $seer, WhoInterface $seeable)
    {
        $respository = $this->get('vimeet_infrastructure.repository.see_repository');

        return $respository->getByEventSeerAndSeeable($event, $seer, $seeable) ? :
            $respository->add(new See($event, $seer, $seeable, []));
    }

    /**
     * @param $identifier
     * @param $id
     *
     * @return WhoInterface
     */
    private function findWho($identifier, $id)
    {
        return $this
            ->getDoctrine()
            ->getRepository(sprintf('Entity:%s', ucfirst($identifier)))
            ->find($id);
    }

    /**
     * @param See    $see
     * @param string $locale
     *
     * @return \Symfony\Component\Form\Form
     */
    private function createWhatForm(See $see, $locale)
    {
        $form = $this->createForm(new WhatType(), $see->getWhat(), [
            'action' => $this->generateUrl( 'admin_who_see_who_dont_see_what', [
                'id'          => $see->getEvent()->getId(),
                'seerType'    => $see->getSeer()->getIdentifier(),
                'seerId'      => $see->getSeer()->getId(),
                'seeableType' => $see->getSeeable()->getIdentifier(),
                'seeableId'   => $see->getSeeable()->getId(),
            ]),
            'method' => 'POST',
            'who'    => $see->getSeeable(),
            'locale' => $locale,
        ]);
        $form->add('submit', 'submit');

        return $form;
    }
}
