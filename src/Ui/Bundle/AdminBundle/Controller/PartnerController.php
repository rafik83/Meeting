<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Partner\Create;
use Proximum\Vimeet\Application\Command\Partner\Update;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PartnerController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $create = new Create($this->getUser());
        $events = $this->get('vimeet_infrastructure.repository.event_repository')->getEventsByAdmin($this->getUser());

        $form = $this->createForm(CreateType::class, $create, [
            'submit' => true,
            'events' => $events,
            'user'   => $this->getUser(),
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($create);
                $this->addFlash('success', 'flash.admin.partner.create.success');

                if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                    return $this->redirectToRoute('admin_list_admin');
                }

                return $this->redirectToRoute('admin_list_operator');
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale())
                );
            }
        }

        return $this->render('AdminBundle:Partner:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Admin   $partner
     *
     * @return Response
     */
    public function updateAction(Request $request, Admin $partner)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$partner->isPartner()) {
            throw $this->createAccessDeniedException('Only partner can be updated with this page');
        }

        $events = $this->get('vimeet_infrastructure.repository.event_repository')->getEventsByAdmin($this->getUser());

        $update = new Update($partner);
        $form   = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
            'events' => $events,
            'user'   => $partner,
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($update);
                $this->addFlash('success', 'flash.admin.partner.update.success');

                if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                    return $this->redirectToRoute('admin_list_admin');
                }

                return $this->redirectToRoute('admin_list_operator');
            } catch (EmailAlreadyExistsException $ex) {
                $form->get('email')->addError(
                    $this->get('error_factory')->create('validators.emailAlreadyExist', $request->getLocale())
                );
            }
        }

        return $this->render('AdminBundle:Partner:update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
