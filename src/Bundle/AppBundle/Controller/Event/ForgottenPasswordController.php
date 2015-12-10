<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use DateTime;
use Elastica\Exception\NotFoundException;
use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Proximum\Vimeet\Application\Command\User\NewPassword;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\User\ForgottenPasswordType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\User\NewPasswordType;
use Proximum\Vimeet\Domain\Model\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\View\EventView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgottenPasswordController extends BaseController
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return RedirectResponse|Response
     */
    public function forgottenPasswordAction(Request $request, EventView $eventView)
    {
        $subdomain = $request->attributes->get('subdomain');

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event', ['subdomain' => $subdomain]);
        }

        $forgottenPassword = new ForgottenPassword($eventView, $request->getLocale());

        $form = $this->createForm(ForgottenPasswordType::class, $forgottenPassword, [
            'action' => $this->generateUrl('event_forgotten_password', ['subdomain' => $subdomain]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.user.forgotten_password_handler')->handle($forgottenPassword);

                $this->addFlash('success', 'flash.reset_password_token.success');

                return $this->redirectToRoute('event', [
                    'subdomain' => $subdomain,
                ]);
            } catch (EmailDoesNotExistException $exception) {
                $this->addGivenErrorOnGivenField(
                    $this->get('translator')->trans('validators.emailDoesNotExist', [], 'validators'),
                    $form->get('email')
                );
            }
        }

        return $this->render('VimeetAppBundle:Event/ResetPassword:request_token.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter(
     *   "forgottenPasswordToken",
     *   class="Proximum\Vimeet\Domain\Model\ForgottenPasswordToken",
     *   options={"token" = "token"}
     * )
     *
     * @param Request                $request
     * @param EventView              $eventView
     * @param ForgottenPasswordToken $forgottenPasswordToken
     *
     * @return RedirectResponse|Response
     */
    public function createNewPasswordAction(Request $request, EventView $eventView, ForgottenPasswordToken $forgottenPasswordToken)
    {
        if (new DateTime() > $forgottenPasswordToken->getExpireDate()) {
            throw new NotFoundException('Date of the token expired');
        }

        $subdomain   = $request->attributes->get('subdomain');
        $newPassword = new NewPassword($forgottenPasswordToken->getUser());

        $form = $this->createForm(NewPasswordType::class, $newPassword, [
            'action' => $this->generateUrl('event_create_new_password', [
                'subdomain' => $subdomain,
                'token'     => $forgottenPasswordToken->getToken(),
            ]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.user.new_password_handler')->handle($newPassword);
            $this->authenticate($forgottenPasswordToken->getUser());

            $this->addFlash('success', 'flash.new_password.success');

            return $this->redirectToRoute('event', [
                'subdomain' => $subdomain,
            ]);
        }

        return $this->render('VimeetAppBundle:Event/ResetPassword:new_password.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }
}
