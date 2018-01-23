<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
use Symfony\Component\Security\Core\User\UserInterface;

class HomeController extends Controller
{
    /**
     * Event home.
     *
     * @param Request            $request
     * @param EventDomain        $eventDomain
     * @param null|UserInterface $user
     *
     * @return RedirectResponse|Response
     */
    public function indexAction(Request $request, EventDomain $eventDomain, UserInterface $user = null)
    {
        $locale = $request->getLocale();
        $event  = $eventDomain->getEvent();

        $response = $this
            ->get('infrastructure.route.home_dispatch.home_user_dispatcher')
            ->attemptDispatchUser($event, $user);

        if ($response instanceof RedirectResponse) {
            return $response;
        }

        $form = $this->createForm(TypeChoiceType::class, null, [
            'locale' => $locale,
            'event'  => $event,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $typeView = $form->getData()['type'];

            if (null === $typeView) {
                $form->get('type')->addError(
                    new FormError($this->get('translator')->trans('validators.type.required', [], 'validators'))
                );
            } else {
                if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                    return $this->redirectToRoute('event_participate', ['typeView' => $typeView->id]);
                }

                return $this->redirectToRoute('event_register', ['typeView' => $typeView->id]);
            }
        }

        return $this->render('EventBundle:Home:index.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
