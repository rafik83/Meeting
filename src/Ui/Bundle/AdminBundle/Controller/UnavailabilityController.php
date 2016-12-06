<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Unavailability\Category\Create;
use Proximum\Vimeet\Application\Command\Unavailability\Category\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UnavailabilityController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function categoryListAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $categories = $this->get('repository.unavailability.category_repository')->findByEvent($event);

        return $this->render('AdminBundle:Unavailability/Category:list.html.twig', [
            'event'      => $event,
            'categories' => $categories,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response|RedirectResponse
     */
    public function createCategoryAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new Create($event);
        $form = $this->createForm(CreateType::class, $create, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_unavailability_category_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Unavailability/Category:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Event    $event
     * @param Category $category
     *
     * @return RedirectResponse|Response
     */
    public function updateCategoryAction(Request $request, Event $event, Category $category)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $category->getEvent()) {
            throw $this->createNotFoundException('This category is not on this event');
        }

        $update = new Update($category);
        $form = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);

            return $this->redirectToRoute('admin_unavailability_category_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Unavailability/Category:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
