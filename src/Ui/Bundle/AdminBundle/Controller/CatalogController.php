<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Catalog\External\Configure;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Catalog\ConfigureType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends Controller
{
    /**
     * @param Request $request
     * @param Event $event
     *
     * @return Response
     *
     */
    public function configureAction(Request $request, Event $event)
    {
        $locale = $event->getAvailableLocale($request->getLocale());

        $configure = new Configure($event, true);

        $configureForm = $this->createForm(ConfigureType::class, $configure, [
            'user' => $this->getUser(),
            'event'     => $event,
            'locale'    => $locale,
        ]);

        if ($configureForm->handleRequest($request)->isSubmitted() && $configureForm->isValid()) {
            
        }

        return $this->render('@Admin/Catalog/External/configure.html.twig', [
            'event' => $event,
            'form' => $configureForm->createView(),
        ]);
    }
}
