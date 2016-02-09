<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\User\ChangeMail;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\User\ChangeMailType;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response|RedirectResponse
     */
    public function updateAction(Request $request, EventView $eventView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $changeMail = new ChangeMail($this->getUser());

        $form = $this->createForm(ChangeMailType::class, $changeMail, [
            'action' => $this->generateUrl('event_account'),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        return $this->render('VimeetAppBundle:Event/User:update_account.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }
}
