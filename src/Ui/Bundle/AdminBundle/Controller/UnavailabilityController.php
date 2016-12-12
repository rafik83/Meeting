<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Unavailability\Category\Create as CreateCategory;
use Proximum\Vimeet\Application\Command\Unavailability\Category\Update as UpdateCategory;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Create as CreateMass;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Update as UpdateMass;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\CreateType as CreateCategoryType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\UpdateType as UpdateCategoryType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Mass\CreateType as CreateMassType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Mass\UpdateType as UpdateMassType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UnavailabilityController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function massListAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $massList = $this->get('repository.unavailability.mass_repository')->findByEvent(
            $event,
            $event->getAvailableLocale($request->getLocale())
        );

        return $this->render('AdminBundle:Unavailability/Mass:list.html.twig', [
            'event'    => $event,
            'massList' => $massList,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createMassAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $days = $event->getDays();
        $day  = reset($days);

        if ($day === false) {
            $day = null;
        }

        $create = new CreateMass($event, $day);
        $form   = $this->createForm(CreateMassType::class, $create, [
            'event'  => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.unavailability.mass.create.success');

            return $this->redirectToRoute('admin_unavailability_mass_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Unavailability/Mass:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Mass    $mass
     *
     * @return RedirectResponse|Response
     */
    public function updateMassAction(Request $request, Event $event, Mass $mass)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $mass->getEvent()) {
            throw $this->createNotFoundException('This mass is not on this event');
        }

        $update = new UpdateMass($mass);
        $form = $this->createForm(UpdateMassType::class, $update, [
            'event'  => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.unavailability.mass.update.success');

            return $this->redirectToRoute('admin_unavailability_mass_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Unavailability/Mass:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

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

        $create = new CreateCategory($event);
        $form = $this->createForm(CreateCategoryType::class, $create, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.unavailability.category.create.success');

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

        $update = new UpdateCategory($category);
        $form = $this->createForm(UpdateCategoryType::class, $update, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.unavailability.category.update.success');

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
