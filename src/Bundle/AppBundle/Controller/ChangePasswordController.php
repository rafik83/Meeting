<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Proximum\Vimeet\Application\Command\User\ChangePassword;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\ChangePasswordType;
use Proximum\Vimeet\Domain\Model\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChangePasswordController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response|RedirectResponse
     */
    public function changePasswordAction(Request $request, EventView $eventView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $subdomain = $request->attributes->get('subdomain');

        $changePassword = new ChangePassword($this->getUser(), null, null);

        $form = $this->createForm(new ChangePasswordType(), $changePassword, [
            'action' => $this->generateUrl('event_change_password', ['subdomain' => $subdomain]),
            'method' => 'POST',
        ]);

        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.application.command.user.change_password')->handle($changePassword);

            $this->addFlash('success', 'flash.change_password.success');

            return $this->redirectToRoute('event', [
                'subdomain' => $subdomain,
            ]);
        }

        return $this->render('VimeetAppBundle:ChangePassword:change_password.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }
}
