<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
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
        $event = $eventDomain->getEvent();

        if ($user === null) {
            $response = $this->attemptDispatchUnLoggedUser($event);
        } else {
            $response = $this->attemptDispatchLoggedUser($event, $user);
        }

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

    /**
     * @param Event              $event
     * @param null|UserInterface $user
     *
     * @return null|RedirectResponse
     */
    private function attemptDispatchLoggedUser(Event $event, UserInterface $user = null)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED') && $user instanceof User) {
            $homeDispatchView = $this->get('components.home.home_dispatch')->handle($event, $user);

            if (null !== $homeDispatchView) {
                if ($homeDispatchView->isGroup()) {
                    return $this->redirectToRoute(
                        'event_sheet_group_index',
                        ['sheetGroup' => $homeDispatchView->getGroup()->getId()]
                    );
                }

                if ($homeDispatchView->isOneSheet()) {
                    return $this->redirectToRoute(
                        'event_sheet_default',
                        ['sheet' => $homeDispatchView->getSheet()->getId()]
                    );
                }

                if ($homeDispatchView->isMultipleSheet()) {
                    return $this->redirectToRoute('event_select_sheet');
                }
            }
        }

        return null;
    }

    /**
     * @param Event $event
     *
     * @return null|RedirectResponse
     */
    private function attemptDispatchUnloggedUser(Event $event): ?RedirectResponse
    {
        $homeDispatchView = $this->get('components.home.home_dispatch_anonymous_user')->handle($event);

        if (null !== $homeDispatchView) {
            if ($homeDispatchView->isRegistrationNotOpen() || $homeDispatchView->isRegistrationClosed()) {

                return $this->redirectToRoute('event_waiting_page');
            }
        }

        return null;
    }
}
