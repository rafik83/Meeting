<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Happening\Category\Create;
use Proximum\Vimeet\Application\Command\Happening\Category\Update;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening\Category\CategoryCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening\Category\CategoryUpdateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Happening\CreateType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HappeningController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $happenings = $this
            ->get('vimeet_infrastructure.repository.happening_repository')
            ->findByEvent($event, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Happening:list.html.twig', [
            'event'      => $event,
            'happenings' => $happenings,
        ]);
    }

    public function createAction(Request $request, Event $event)
    {
        $create = new \Proximum\Vimeet\Application\Command\Happening\Create($event);
        $form   = $this->createForm(CreateType::class, $create, [
            'event'  => $event,
            'action' => $this->generateUrl('admin_happening_create', ['id' => $event->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.happening.create_handler')->handle($create);
            $this->addFlash('success', 'flash.admin.happening.create.success');

            return $this->redirectToRoute('admin_happening_list', [
                'id' => $event->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/Happening:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listCategoryAction(Request $request, Event $event)
    {
        $categories = $this
            ->get('vimeet_infrastructure.repository_happening.category_repository')
            ->findByEvent($event, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Happening/Category:list.html.twig', [
            'event'      => $event,
            'categories' => $categories,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function createCategoryAction(Request $request, Event $event)
    {
        $create = new Create($event);
        $form   = $this->createForm(CategoryCreateType::class, $create, [
            'action' => $this->generateUrl('admin_happening_category_create', ['id' => $event->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.happening.category.create_handler')->handle($create);
            $this->addFlash('success', 'flash.admin.happening.category.create.success');

            return $this->redirectToRoute('admin_happening_category_list', [
                'id' => $event->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/Happening/Category:create.html.twig', [
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
        if ($event !== $category->getEvent()) {
            throw $this->createNotFoundException('Category not found.');
        }

        $update = new Update($category);
        $form   = $this->createForm(CategoryUpdateType::class, $update, [
            'action' => $this->generateUrl('admin_happening_category_update', ['id' => $event->getId(), 'category' => $category->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.happening.category.update_handler')->handle($update);
            $this->addFlash('success', 'flash.admin.happening.category.update.success');

            return $this->redirectToRoute('admin_happening_category_list', [
                'id' => $event->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/Happening/Category:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
