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
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Operator\FilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OperatorController extends Controller
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
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');
        $organizer = $this->getUser();
        if (!$organizer->isOrganizer()) {
            throw $this->createAccessDeniedException(
                sprintf('%s is not a granted ROLE to access this page', $organizer->getRole())
            );
        }

        $filters    = [];
        $filtered   = false;
        $filterForm = $this->createFilterForm(
            FilterType::class,
            ['event' => $request->query->get('event')],
            ['events' => $organizer->getEvents()]
        );

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters   = $filterForm->getData();
            $filtered = true;
        }

        $operators = $this->get('repository.admin_repository')
            ->getOperatorForOrganizer(
                $organizer,
                $request->query->get('page', 1),
                20,
                $filters
            )
        ;

        return $this->render('VimeetAppBundle:Admin/Operator:list.html.twig', [
            'operators'   => $operators,
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
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');
        $organizer = $this->getUser();
        if (!$organizer->isOrganizer()) {
            throw $this->createAccessDeniedException(sprintf('%s is not a granted ROLE to access this page', $organizer->getRole()));
        }

        $create = new Create($this->getUser(), new \DateTime());

        $form = $this->createForm(CreateType::class, $create, [
            'action' => $this->generateUrl('admin_create_operator'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('command.operator.create_handler')->handle($create);
                $this->addFlash('success', 'flash.admin.operator.create.success');

                return $this->redirectToRoute('admin_list_operator');
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError($this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale()));
            }
        }

        return $this->render('VimeetAppBundle:Admin/Operator:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
