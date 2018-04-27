<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Admin\ChangePassword;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\ChangePasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('AdminBundle:Account:index.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function updatePasswordAction(Request $request)
    {
        $changePassword = new ChangePassword($this->getUser());

        $form = $this->createForm(ChangePasswordType::class, $changePassword, [
            'action' => $this->generateUrl('admin_update_password'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($changePassword);
            $this->addFlash('success', 'flash.admin.change_password.success');

            return $this->redirectToRoute('admin_account');
        }

        return $this->render('AdminBundle:Account:updatePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
