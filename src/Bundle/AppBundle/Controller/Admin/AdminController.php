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
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Admin\CreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Admin\FilterAdminType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

class AdminController extends Controller
{
    /**
     * @param string $type
     * @param string $data
     * @param array  $options
     *
     * @return Form|FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, $options);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $filters = [];
        $filtered   = false;
        $filterForm = $this->createFilterForm(
            FilterAdminType::class,
            [
                'role'  => $request->query->get('role'),
                'event' => $request->query->get('event'),
            ]
        );

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters   = $filterForm->getData();
            $filtered = true;
        }
        $admins = $this->get('repository.admin_repository')->listPaginated($request->query->get('page', 1), 20, $filters);

        return $this->render('VimeetAppBundle:Admin/Admin:list.html.twig', [
            'admins'      => $admins,
            'filter_form' => $filterForm->createView(),
            'filtered'    => $filtered
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
            try {
                $this->get('command.admin.create_handler')->handle($create);
                $this->addFlash('success', 'flash.admin.admin.create.success');

                return $this->redirectToRoute('admin_list_admin');
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError($this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale()));
            }

        }

        return $this->render('VimeetAppBundle:Admin/Admin:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
