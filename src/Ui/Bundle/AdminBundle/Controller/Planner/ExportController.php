<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planner;

use Proximum\Vimeet\Application\Exception\Planner\DayNotConfiguredException;
use Proximum\Vimeet\Application\Exception\Planner\SlotNotConfiguredException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\XmlFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ExportController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return XmlFileResponse|RedirectResponse
     */
    public function exportAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        try {
            $content  = $this->get('service_planner.exporter')->getXML($event, $request->getLocale());
            $response = new XmlFileResponse(
                $content,
                sprintf("export_planner_%s_%s.xml", $event->getId(), date("Y_m_d_His"))
            );

            return $response;
        } catch(SlotNotConfiguredException $exception) {
            $this->addFlash('error', sprintf('flash.%s', $exception->getMessage()));

            return $this->redirectToRoute('admin_planner', ['event' => $event->getId()]);
        } catch(DayNotConfiguredException $exception) {
            $this->addFlash('error', sprintf('flash.%s', $exception->getMessage()));

            return $this->redirectToRoute('admin_planner', ['event' => $event->getId()]);
        }
    }
}
