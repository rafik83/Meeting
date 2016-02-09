<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\TypeTemplateField\UpdateLibChoice;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library\Admin\ChoiceType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeTemplateFieldController extends Controller
{
    /**
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

        $packageObject = $this->get('vimeet_infrastructure.application.components.product.product_builder')
            ->createFromType($type);

        $templateFactory = $this->container->get('components.sheet.template_factory');
        $templates = [
            'participant' => $templateFactory->createTemplateFromArray($type->getParticipantTemplate()),
            'sheet'       => $templateFactory->createTemplateFromArray($type->getSheetTemplate()),
            'package'     => $templateFactory->createTemplateFromArray($type->getPackageTemplate()),
        ];

        return $this->render('VimeetAppBundle:Admin/TypeTemplateField:list.html.twig', [
            'event'         => $event,
            'typeView'      => $typeView,
            'packageObject' => $packageObject,
            'templates'     => $templates,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     * @param         $templateName
     * @param         $group
     * @param         $row
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function fieldUpdateAction(Request $request, Event $event, Type $type, $templateName, $group, $row)
    {
        $typeView = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewById($type->getId(), $request->getLocale());

        $templateFactory = $this->container->get('components.sheet.template_factory');
        $template = $templateFactory->createTemplateFromArray($type->getTemplate($templateName));
        $group = $template->getGroup($group);
        $field = $group->getType($row);

        if (!$field->isEditable()) {
            throw $this->createAccessDeniedException('This field is not editable');
        }

        $update = new UpdateLibChoice($type, $templateName, $field);

        $form = $this->createForm(ChoiceType::class, $update, [
            'method'  => 'POST',
            'locales' => $event->getLocales(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.type_template_field.update_choice_handler')
                ->handle($update);

            $this->addFlash('success', 'flash.admin.type_template_field.update.success');

            return $this->redirectToRoute('admin_type_template_field_list', [
                'event' => $event->getId(),
                'type'  => $type->getId(),
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
