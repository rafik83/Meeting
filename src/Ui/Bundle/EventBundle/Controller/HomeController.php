<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Type\TypeChoiceType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * Event home.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request, EventDomain $eventDomain)
    {
        $locale = $request->getLocale();

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $sheets = $this
                ->get('vimeet_infrastructure.repository.sheet_repository')
                ->getSheetsByUserAndEvent($this->getUser()->getId(), $eventDomain->getEvent());
        } else {
            $sheets = [];
        }

        $form = $this->createForm(TypeChoiceType::class, null, [
            'locale' => $locale,
            'event'  => $eventDomain->getEvent(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $typeView = $form->getData()['type'];

            if (null === $typeView) {
                $form->get('type')->addError(
                    new FormError($this->get('translator')->trans('validators.type.required', [], 'validators'))
                );
            } else {
                if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
                    return $this->redirectToRoute('event_participate', ['typeView' => $typeView->id]);
                }

                return $this->redirectToRoute('event_register', ['typeView' => $typeView->id]);
            }
        }

        return $this->render('EventBundle:Home:index.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheets'    => $sheets,
            'form'      => $form->createView(),
        ]);
    }
}
