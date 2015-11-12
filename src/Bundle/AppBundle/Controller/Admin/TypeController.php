<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Type\Update;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Type\UpdateType;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TypeController extends Controller
{
    public function listAction(Request $request)
    {
        $types = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->paginate($request->query->get('page', 1), 20, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Type:list.html.twig', [
            'types' => $types,
        ]);
    }

    public function updateAction(Request $request, Type $type)
    {
        $update = new Update($type);
        $form   = $this->createForm(new UpdateType(), $update, [
            'action' => $this->generateUrl('admin_type_update', ['id' => $type->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.type.update_handler')->handle($update);
            $this->addFlash('success', 'flash.admin.type.update.success');

            return $this->redirectToRoute('admin_type_update', ['id' => $type->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Type:update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
