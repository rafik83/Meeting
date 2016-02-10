<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Spot\BatchCreate;
use Proximum\Vimeet\Application\Command\Spot\Create;
use Proximum\Vimeet\Application\Command\Spot\EnableBatch;
use Proximum\Vimeet\Application\Exception\Spot\MultipleUniqueReferenceViolationException;
use Proximum\Vimeet\Application\Exception\Spot\UniqueReferenceViolationException;
use Proximum\Vimeet\Application\Command\Spot\DeleteBatch;
use Proximum\Vimeet\Application\Command\Spot\DisableBatch;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Spot\FilterSpotType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Spot\SpotCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Spot\BatchCreateType;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

class SpotController extends Controller
{
    /**
     * @param string $type
     * @param string $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, $options);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $filter     = [];
        $filterForm = $this->createFilterForm(FilterSpotType::class, $filter);

        if ($filterForm->handleRequest($request)->isSubmitted() && $filterForm->isValid()) {
            $filter = $filterForm->getData();
        }

        $spots = $this->get('vimeet_infrastructure.repository.spot_repository')->getSpotFilter($event, $filter);

        return $this->render(
            'VimeetAppBundle:Admin/Spot:list.html.twig', [
            'spots'               => $spots,
            'event'               => $event,
            'filter_form'         => $filterForm->createView(),
            'filtered'            => $filterForm->isSubmitted() && $filterForm->isValid(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse
     */
    public function batchAction(Request $request, Event $event)
    {
        $spotsToDelete = $request->request->get('ids', []);
        $deleteButton  = $request->request->getBoolean('delete');
        $disableButton = $request->request->getBoolean('disable');
        $enableButton  = $request->request->getBoolean('enable');

        if (!empty($spotsToDelete)) {
            if ($deleteButton) {
                $deleteBatch = new DeleteBatch($spotsToDelete, $event);
                $this->get('vimeet_infrastructure.vimeet.application.command.spot.delete_batch_handler')->handle($deleteBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.delete.success');
            } elseif ($disableButton) {
                $disableBatch = new DisableBatch($spotsToDelete, $event);
                $this->get('vimeet_infrastructure.vimeet.application.command.spot.disable_batch_handler')->handle($disableBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.disable.success');
            } elseif ($enableButton) {
                $enableBatch = new EnableBatch($spotsToDelete, $event);
                $this->get('vimeet_infrastructure.vimeet.application.command.spot.enable_batch_handler')->handle($enableBatch);
                $this->addFlash('success', 'flash.admin.spot_batch.enable.success');
            }
        }

        return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event)
    {
        $create = new Create($event);
        $form = $this->createForm(SpotCreateType::class, $create, [
            'action' => $this->generateUrl('admin_spot_create', ['event' => $event->getId()]),
            'method'=> 'POST'
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.spot.create_handler')->handle($create);
                $this->addFlash('success', 'flash.admin.spot.create.success');

                return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
            } catch (UniqueReferenceViolationException $exception) {
                $form->get('reference')->addError(new FormError($this->get('translator')->trans('validators.spot.reference.unique', [], 'validators')));
            }
        }

        return $this->render('VimeetAppBundle:Admin/Spot:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function batchCreateAction(Request $request, Event $event)
    {
        $batchCreate = new BatchCreate($event);
        $form        = $this->createForm(BatchCreateType::class, $batchCreate);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('command.spot.batch_create_handler')->handle($batchCreate);
                $this->addFlash('success', 'flash.admin.spot.batch_create.success');
            } catch (MultipleUniqueReferenceViolationException $exception) {
                $this->addFlash('warning', [
                    'message'   => 'flash.admin.spot.batch_create.duplicate',
                    'arguments' => ['%references%' => $exception->getReferencesAsString()],
                ]);
            }

            return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Spot:batchCreate.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
