<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Admin\Create;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Admin\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $admins = $this->get('repository.admin_repository')->listPaginated($request->query->get('page', 1), 20);

        return $this->render('VimeetAppBundle:Admin/Admin:list.html.twig', [
            'admins' => $admins
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $create = new Create($request->getLocale());

        $form = $this->createForm(CreateType::class, $create, [
            'action' => $this->generateUrl('admin_create_admin'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.admin.create_handler')->handle($create);
            $this->addFlash('success', 'flash.admin.admin.create.success');

            return $this->redirectToRoute('admin_list_admin');
        }

        return $this->render('VimeetAppBundle:Admin/Admin:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
