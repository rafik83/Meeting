<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

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

        $packageObject = $this
            ->get('vimeet_infrastructure.application.components.product.product_builder')
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
     * @param string  $templateName
     * @param string  $group
     * @param string  $libType
     *
     * @return RedirectResponse|Response
     * @throws \Exception
     */
    public function fieldAddAction(Request $request, Event $event, Type $type, $templateName, $group, $libType)
    {
        $typeView = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewById($type->getId(), $request->getLocale());

        $templateFactory = $this->get('components.sheet.template_factory');
        $template = $templateFactory->createTemplateFromArray($type->getTemplate($templateName));
        $group = $template->getGroup($group);

        $field = $templateFactory->createType($libType);
        $field->setGroup($group);

        foreach ($event->getLocales() as $locale) {
            $field->setLocaleLabel($locale, '');
        }

        $command = $this
            ->get('vimeet_infrastructure.vimeet.application.command.type_template_field.update_factory')
            ->getCommand($field->getRawType());

        $update = new $command($type, $templateName, $field);

        $formClassType = $this
            ->get('vimeet_app.form_type_admin.library.type_factory')
            ->getForm($libType);

        $form = $this->createForm($formClassType, $update, [
            'method'  => 'POST',
            'locales' => $event->getLocales(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.type_template_field.update_handler_factory')
                ->getHandler($field->getRawType())
                ->handle($update);

            $this->addFlash('success', 'flash.admin.type_template_field.update.success');

            return $this->redirectToRoute('admin_type_template_field_list', [
                'event' => $event->getId(),
                'type'  => $type->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/TypeTemplateField:add.html.twig', [
            'event'    => $event,
            'typeView' => $typeView,
            'type'     => $type,
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     * @param string  $templateName
     * @param string  $group
     * @param string  $row
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

        $command = $this
            ->get('vimeet_infrastructure.vimeet.application.command.type_template_field.update_factory')
            ->getCommand($field->getRawType());

        $update = new $command($type, $templateName, $field);

        $formClassType = $this
            ->get('vimeet_app.form_type_admin.library.type_factory')
            ->getForm($field->getRawType());

        $form = $this->createForm($formClassType, $update, [
            'method'  => 'POST',
            'locales' => $event->getLocales(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.type_template_field.update_handler_factory')
                ->getHandler($field->getRawType())
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
