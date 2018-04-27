<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Operator\Create;
use Proximum\Vimeet\Application\Command\Operator\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator\FilterType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OperatorController extends Controller
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
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $filters    = ['event' => $request->query->get('event')];
        $filterForm = $this->createFilterForm(FilterType::class, $filters, [
            'events' => $this->getUser()->getEvents(),
        ]);

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();
        }

        $operators = $this->get('repository.admin_repository')->getOperatorForOrganizer(
            $this->getUser(),
            $request->query->getInt('page', 1),
            20,
            $filters
        );

        return $this->render('AdminBundle:Operator:list.html.twig', [
            'operators'   => $operators,
            'filter_form' => $filterForm->createView(),
            'filtered'    => $filterForm->isSubmitted() && $filterForm->isValid(),
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

        $create = new Create($this->getUser(), new \DateTime());
        $form   = $this->createForm(CreateType::class, $create, [
            'action' => $this->generateUrl('admin_create_operator'),
            'method' => 'POST',
            'submit' => true,
            'events' => $this->getUser()->getEvents(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($create);
                $this->addFlash('success', 'flash.admin.operator.create.success');

                return $this->redirectToRoute('admin_list_operator');
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale())
                );
            }
        }

        return $this->render('AdminBundle:Operator:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Admin   $operator
     * @param Request $request
     *
     * @return Response
     */
    public function updateAction(Request $request, Admin $operator)
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        if (!$operator->isOperator()) {
            throw $this->createAccessDeniedException('Only operator can be updated with this page');
        }

        $eventsAllowedByAdmin = $this->getUser()->getEvents()->toArray();

        $update = new Update($operator, $eventsAllowedByAdmin);
        $form   = $this->createForm(UpdateType::class, $update, [
            'action' => $this->generateUrl('admin_update_operator', ['operator' => $operator->getId()]),
            'method' => 'POST',
            'submit' => true,
            'events' => $eventsAllowedByAdmin,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($update);
                $this->addFlash('success', 'flash.admin.operator.update.success');

                return $this->redirectToRoute('admin_list_operator');
            } catch (EmailAlreadyExistsException $ex) {
                $error = $this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale());
                $form->get('email')->addError($error);
            }
        }

        return $this->render('AdminBundle:Operator:update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
