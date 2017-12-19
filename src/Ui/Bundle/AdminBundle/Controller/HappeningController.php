<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Happening\Category\Create as CreateCategory;
use Proximum\Vimeet\Application\Command\Happening\Category\Update as UpdateCategory;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\Query\Happening\Admin\HappeningParticipantViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Category\CategoryCreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening\Category\CategoryUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class HappeningController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listCategoryAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $categories = $this
            ->get('vimeet_infrastructure.repository_happening.category_repository')
            ->findByEvent($event, $event->getAvailableLocale($request->getLocale()));

        return $this->render('AdminBundle:Happening/Category:list.html.twig', [
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
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreateCategory($event);
        $form   = $this->createForm(CategoryCreateType::class, $create, [
            'action' => $this->generateUrl('admin_happening_category_create', ['event' => $event->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.happening.category.create.success');

            return $this->redirectToRoute('admin_happening_category_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Happening/Category:create.html.twig', [
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
            throw $this->createNotFoundException('Category not found.');
        }

        $update = new UpdateCategory($category);
        $form   = $this->createForm(CategoryUpdateType::class, $update, [
            'action' => $this->generateUrl('admin_happening_category_update', [
                'event'    => $event->getId(),
                'category' => $category->getId(),
            ]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.happening.category.update.success');

            return $this->redirectToRoute('admin_happening_category_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Happening/Category:update.html.twig', [
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
    public function exportAction(Request $request, Event $event)
    {
        try {
            $happeningParticipantViews = $this->get('tactician.commandbus.query')->handle(
                new HappeningParticipantViewQuery($event, $event->getAvailableLocale($request->getLocale()))
            );
        } catch (EmptyHappeningParticipationException $exception) {
            $this->addFlash('error', 'flash.admin.happening.participation.empty');

            return $this->redirectToRoute('admin_happening_list', ['event' => $event->getId()]);
        }

        $serializer      = $this->get('serializer');
        $exportedContent = $serializer->serialize($happeningParticipantViews, 'csv', [
            'locale'        => $event->getAvailableLocale($request->getLocale()),
            'charset'       => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ]);

        $response    = new Response($exportedContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "export_happening_participants_" . date("Y_m_d_His") . ".csv"
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', Charset::WINDOWS_1252));

        return $response;
    }
}
