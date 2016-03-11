<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Operator\Create;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Operator\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OperatorController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $create = new Create($this->getUser());

        $form = $this->createForm(CreateType::class, $create, [
            'action' => $this->generateUrl('admin_create_operator'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('command.operator.create_handler')->handle($create);
                $this->addFlash('success', 'flash.admin.operator.create.success');

                return $this->redirectToRoute('admin_event_list');
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError($this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale()));
            }
        }

        return $this->render('VimeetAppBundle:Admin/Operator:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
