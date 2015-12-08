<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Category\Create;
use Proximum\Vimeet\Application\Command\Category\Update;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategoryController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $categories = $this
            ->get('vimeet_infrastructure.repository.category_repository')
            ->paginate($request->query->get('page', 1), 20, $event->getId(), $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Category:list.html.twig', [
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
    public function createAction(Request $request, Event $event)
    {
        $create = new Create($event);
        $form   = $this->createForm('category', $create, [
            'method' => 'POST',
            'event'  => $event,
            'locale' => $request->getLocale(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.category.create_handler')->handle($create);
            $this->addFlash('success', 'flash.admin.category.create.success');

            return $this->redirectToRoute('admin_category_list', [
                'id' => $event->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/Category:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter(
     *   "category",
     *   class="Proximum\Vimeet\Domain\Model\Category",
     *   options={"id" = "category_id"}
     * )
     *
     * @param Request  $request
     * @param Event    $event
     * @param Category $category
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, Category $category)
    {
        if ($event !== $category->getEvent()) {
            throw $this->createNotFoundException('Category not found.');
        }

        $update = new Update($category);
        $form   = $this->createForm('category', $update, [
            'method' => 'POST',
            'event'  => $event,
            'locale' => $request->getLocale(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.category.update_handler')->handle($update);
            $this->addFlash('success', 'flash.admin.category.update.success');

            return $this->redirectToRoute('admin_category_list', [
                'id' => $event->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/Category:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
