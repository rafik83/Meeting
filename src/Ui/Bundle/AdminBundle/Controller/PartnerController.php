<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Partner\Create;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner\CreateType;
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
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');

        $create = new Create($this->getUser());

        $form = $this->createForm(CreateType::class, $create, [
            'submit' => true,
            'events' => $this->getUser()->getEvents()->toArray(),
            'user'   => $this->getUser(),
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

        return $this->render('AdminBundle:Partner:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
