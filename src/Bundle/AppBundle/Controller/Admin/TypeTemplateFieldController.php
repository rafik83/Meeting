<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\TypeTemplateField\Update;
use Proximum\Vimeet\Application\Command\TypeTemplateField\UpdateChoice;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\TypeTemplateField\UpdateType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeTemplateFieldController extends Controller
{
    /**
     * @ParamConverter(
     *   "type",
     *   class="Proximum\Vimeet\Domain\Model\Type",
     *   options={"id" = "type_id"}
     * )
     *
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event, Type $type)
    {
        $typeView = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewById($type->getId(), $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/TypeTemplateField:list.html.twig', [
            'event'    => $event,
            'typeView' => $typeView,
            'type'     => $type,
        ]);
    }

    /**
     * @ParamConverter(
     *   "type",
     *   class="Proximum\Vimeet\Domain\Model\Type",
     *   options={"id" = "type_id"}
     * )
     *
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     * @param string  $template
     * @param string  $key
     *
     * @return Response|RedirectResponse
     *
     * @throws \Exception
     */
    public function fieldUpdateAction(Request $request, Event $event, Type $type, $template, $key)
    {
        $typeView = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewById($type->getId(), $request->getLocale());

        $update = new UpdateChoice($type, $template, $key);

        $form   = $this->createForm('type_template_field_update_lib_choice', $update, [
            'method'  => 'POST',
            'locales' => $event->getLocales(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.type_template_field.update_handler')
                ->handle($update);

            $this->addFlash('success', 'flash.admin.type_template_field.update.success');

            return $this->redirectToRoute('admin_type_template_field_list', [
                'id'      => $event->getId(),
                'type_id' => $type->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/TypeTemplateField:update.html.twig', [
            'event'    => $event,
            'typeView' => $typeView,
            'type'     => $type,
            'update'   => $update,
            'form'     => $form->createView(),
        ]);
    }
}
