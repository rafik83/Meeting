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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeFieldController extends Controller
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
        return $this->render('VimeetAppBundle:Admin/TypeField:list.html.twig', [
            'event'    => $event,
            'template' => $type->getSheetTemplate(),
        ]);
    }
}
