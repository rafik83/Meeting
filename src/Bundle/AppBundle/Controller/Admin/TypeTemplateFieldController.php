<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Type\FieldUpdate;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Type\FieldUpdateType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeFormController extends Controller
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

        return $this->render('VimeetAppBundle:Admin/TypeForm:list.html.twig', [
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
     * @param string  $key
     *
     * @return Response
     */
    public function fieldUpdateAction(Request $request, Event $event, Type $type, $key)
    {
        $typeView = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewById($type->getId(), $request->getLocale());

        $fieldUpdate = new FieldUpdate($type, $key);

        $form   = $this->createForm(new FieldUpdateType(), $fieldUpdate, [
            'method' => 'POST',
        ]);

        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            dump($fieldUpdate);
            exit;
        }

        return $this->render('VimeetAppBundle:Admin/TypeForm:fieldUpdate.html.twig', [
            'event'    => $event,
            'typeView' => $typeView,
            'type'     => $type,
            'form'     => $form->createView(),
        ]);
    }
}
