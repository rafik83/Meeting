<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Spot\Create;
use Proximum\Vimeet\Application\Command\Spot\DeleteBatch;
use Proximum\Vimeet\Application\Command\Spot\DisableBatch;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Spot\FilterSpotType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Spot\SpotCreateType;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class SpotController extends Controller
{
    /**
     * @param string $type
     * @param string $data
     * @param array  $options
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, $options);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request, Event $event)
    {
        $spotsToDelete = $request->request->get('ids', []);
        $deleteButton  = $request->request->getBoolean('delete');
        $disableButton = $request->request->getBoolean('disable');

        if (!empty($spotsToDelete)) {
            if ($deleteButton) {
                $deleteBatch = new DeleteBatch($spotsToDelete, $event);
                $this->get('vimeet_infrastructure.vimeet.application.command.spot.delete_batch_handler')
                    ->handle($deleteBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.delete.success');

                return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);

            } elseif($disableButton) {
                $disableBatch = new DisableBatch($spotsToDelete, $event);
                $this->get('vimeet_infrastructure.vimeet.application.command.spot.disable_batch_handler')
                    ->handle($disableBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.disable.success');

                return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
            }
        }

        $filter   = [];
        $filtered = false;

        $filterForm = $this->createFilterForm(FilterSpotType::class, [
            'reference'       => $request->query->get('reference'),
            'meetingCapacity' => intval($request->query->get('meetingCapacity')),
            'seatCapacity'    => intval($request->query->get('seatCapacity')),
            'size'            => intval($request->query->get('size')),
            'active'          => boolval($request->query->get('active'))
        ]);

        $filterForm->add('submit', SubmitType::class, [
            'label' => 'form.admin.filter_spot_type.children.submit.label'
        ]);

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filter   = $filterForm->getData();
            $filtered = true;
        }

        $spots = $this
            ->get('vimeet_infrastructure.repository.spot_repository')
            ->getSpotFilter($event, $filter);

        return $this->render(
            'VimeetAppBundle:Admin/Spot:list.html.twig', [
            'spots'               => $spots,
            'event'               => $event,
            'filter_form'         => $filterForm->createView(),
            'filtered'            => $filtered,
        ]);
    }

    public function createAction(Request $request, Event $event)
    {
        $create = new Create($event);
        $form = $this->createForm(SpotCreateType::class, $create, [
            'action' => $this->generateUrl('admin_spot_create', ['event' => $event->getId()]),
            'method'=> 'POST'
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.spot.create_handler')->handle($create);
            $this->addFlash('success', 'flash.admin.spot.create.success');

            return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Spot:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
