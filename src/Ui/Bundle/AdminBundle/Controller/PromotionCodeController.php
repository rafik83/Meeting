<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\PromotionCode\Create;
use Proximum\Vimeet\Application\Command\PromotionCode\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromotionCodeController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request, Event $event)
    {
        $create     = new Create($event);
        $createForm = $this->createForm(CreateType::class, $create, ['submit' => true]);

        if ($createForm->handleRequest($request)->isSubmitted() && $createForm->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToUpdate($result->promotionCode);
        }

        $promotionCodes = $this->get('repository.promotion_code_repository')->findByEvent($event);

        return $this->render('AdminBundle:PromotionCode:list.html.twig', [
            'create_form'     => $createForm->createView(),
            'event'           => $event,
            'promotion_codes' => $promotionCodes,
        ]);
    }

    /**
     * @param Request       $request
     * @param Event         $event
     * @param PromotionCode $promotionCode
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, PromotionCode $promotionCode)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->notFoundIfWrongPromotionCodeEvent($event, $promotionCode);

        $update     = new Update($promotionCode);
        $updateForm = $this->createForm(UpdateType::class, $update, ['submit' => true, 'event' => $event]);

        if ($updateForm->handleRequest($request)->isSubmitted() && $updateForm->isValid()) {
            $this->get('tactician.commandbus')->handle($update);

            return $this->redirectToUpdate($promotionCode);
        }

        return $this->render('AdminBundle:PromotionCode:update.html.twig', [
            'promotion_code' => $promotionCode,
            'event'          => $event,
            'update_form'    => $updateForm->createView(),
        ]);
    }

    /**
     * @param PromotionCode $promotionCode
     *
     * @return RedirectResponse
     */
    private function redirectToUpdate(PromotionCode $promotionCode)
    {
        return $this->redirectToRoute('admin_promotion_code_update', [
            'event'         => $promotionCode->getEvent()->getId(),
            'promotionCode' => $promotionCode->getId(),
        ]);
    }

    /**
     * @param Event         $event
     * @param PromotionCode $promotionCode
     */
    protected function notFoundIfWrongPromotionCodeEvent(Event $event, PromotionCode $promotionCode)
    {
        if ($event !== $promotionCode->getEvent()) {
            throw $this->createNotFoundException('Promotion code not found.');
        }
    }
}
