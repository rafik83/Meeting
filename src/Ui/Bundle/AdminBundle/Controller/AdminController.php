<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Admin\Create;
use Proximum\Vimeet\Application\Command\Admin\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\FilterAdminType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @param string $type
     * @param array  $data
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
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

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
        $admins = $this->get('repository.admin_repository')
            ->listPaginated($request->query->get('page', 1), 20, $filters);

        return $this->render('AdminBundle:Admin:list.html.twig', [
            'admins'      => $admins,
            'filter_form' => $filterForm->createView(),
            'filtered'    => $filtered,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $create = new Create($request->getLocale(), new \DateTime());

        $form = $this->createForm(CreateType::class, $create, [
            'action' => $this->generateUrl('admin_create_admin'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($create);
                $this->addFlash('success', 'flash.admin.admin.create.success');

                return $this->redirectToRoute('admin_list_admin');
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale())
                );
            }
        }

        return $this->render('AdminBundle:Admin:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Admin   $admin
     *
     * @return Response
     */
    public function updateAction(Request $request, Admin $admin)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $update = new Update($admin);

        $form = $this->createForm(UpdateType::class, $update, [
            'action' => $this->generateUrl('admin_update_admin', ['admin' => $admin->getId()]),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($update);
                $this->addFlash('success', 'flash.admin.admin.update.success');

                return $this->redirectToRoute('admin_list_admin');
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale())
                );
            }
        }

        return $this->render('AdminBundle:Admin:update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
