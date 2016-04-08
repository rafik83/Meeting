<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\User\ChangeMail;
use Proximum\Vimeet\Application\Command\User\ChangeMailActivation;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Application\Exception\User\SameEmailException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\ChangeMailType;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
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

        $changeMail = new ChangeMail($this->getUser(), $eventView);
        $form       = $this->createForm(ChangeMailType::class, $changeMail, [
            'action' => $this->generateUrl('event_account'),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($changeMail);
                $this->addFlash('success', 'flash.change_mail.success');

                return $this->redirectToRoute('event');
            } catch (EmailAlreadyExistsException $exception) {
                $form->get('mail')->addError(new FormError('validators.emailAlreadyExist'));
            } catch (SameEmailException $exception) {
                $form->get('mail')->addError(new FormError('validators.sameEmail'));
            }
        }

        return $this->render('EventBundle:User:update_account.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param EventView $eventView
     * @param ChangeMailToken $changeMailToken
     *
     * @return RedirectResponse
     */
    public function activateNewMailAction(EventView $eventView, ChangeMailToken $changeMailToken)
    {
        if ($changeMailToken->isExpired(new \DateTime())) {
            throw $this->createNotFoundException('The token expired.');
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $user                 = $changeMailToken->getUser();
        $changeMailActivation = new ChangeMailActivation($changeMailToken);

        $this->get('tactician.commandbus')->handle($changeMailActivation);
        $this->get('adapter.authentication_manager')->authenticate($user, 'main');
        $this->addFlash('success', 'flash.change_mail_activate.success');

        return $this->redirectToRoute('event');
    }
}
