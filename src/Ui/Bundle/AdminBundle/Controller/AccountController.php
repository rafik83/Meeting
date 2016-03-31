<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Admin\ChangePassword;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Admin\ChangePasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('VimeetAppBundle:Admin/Account:index.html.twig');
    }

    public function updatePasswordAction(Request $request)
    {
        $changePassword = new ChangePassword($this->getUser());

        $form = $this->createForm(ChangePasswordType::class, $changePassword, [
            'action' => $this->generateUrl('admin_update_password'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.admin.change_password_handler')->handle($changePassword);
            $this->addFlash('success', 'flash.admin.change_password.success');

            return $this->redirectToRoute('admin_account');
        }

        return $this->render('VimeetAppBundle:Admin/Account:updatePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
