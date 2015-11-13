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
            $see = $this->findOrCreateSee($event, $form->get('seer')->getData(), $form->get('seeable')->getData());

            return $this->redirect($this->generateWhatUrl($see));
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
        $seer = $this->findWho($seerType, $seerId);
        $this->notFoundUnless($seer, 'Seer not found.');

        $seeable = $this->findWho($seeableType, $seeableId);
        $this->notFoundUnless($seeable, 'Seeable not found.');

        $see = $this->findSee($event, $seer, $seeable);
        $this->notFoundUnless($see, 'See not found.');

        $form = $this->createWhatForm($see, $request->getLocale());

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.repository.see_repository')->set($see);
            $this->addFlash('succes', 'admin.event.who_see_what.success');

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
     * @return See|null
     */
    private function findSee(Event $event, WhoInterface $seer, WhoInterface $seeable)
    {
        return $this
            ->get('vimeet_infrastructure.repository.see_repository')
            ->getByEventSeerAndSeeable($event, $seer, $seeable);
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
        return $this->findSee($event, $seer, $seeable) ? :
            $this->get('vimeet_infrastructure.repository.see_repository')->add(new See($event, $seer, $seeable, []));
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
            'action' => $this->generateWhatUrl($see),
            'method' => 'POST',
            'who'    => $see->getSeeable(),
            'locale' => $locale,
        ]);
        $form->add('submit', 'submit');

        return $form;
    }

    /**
     * @param See $see
     *
     * @return string
     */
    private function generateWhatUrl(See $see)
    {
        return $this->generateUrl('admin_who_see_who_dont_see_what', [
            'id'          => $see->getEvent()->getId(),
            'seerType'    => $see->getSeer()->getIdentifier(),
            'seerId'      => $see->getSeer()->getId(),
            'seeableType' => $see->getSeeable()->getIdentifier(),
            'seeableId'   => $see->getSeeable()->getId(),
        ]);
    }

    /**
     * @param mixed  $condition
     * @param string $message
     */
    private function notFoundUnless($condition, $message = 'Not found.')
    {
        if (!$condition) {
            throw $this->createNotFoundException($message);
        }
    }
}
