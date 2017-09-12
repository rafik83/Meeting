<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Spot;

use Proximum\Vimeet\Application\Command\Spot\Import\SpotImport;
use Proximum\Vimeet\Application\Query\Spot\Import\SpotImportPreviewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\SpotImportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ImportController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function importAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->ifEventHasMeetingInterrupImport($event);

        $spotImport = new SpotImport();

        $form = $this->createForm(SpotImportType::class, $spotImport, ['submit' => true]);

        if ($form->handleRequest($request)->isValid() && $form->isSubmitted()) {
            /** @var File $importedFile */
            $importedFile = $this->get('tactician.commandbus')->handle($spotImport);

            return $this->redirectToRoute('admin_spot_import_confirm', [
                'event' => $event->getId(),
                'importedFile' => $importedFile->getId(),
            ]);
        }

        return $this->render('AdminBundle:Spot\Import:spotImport.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param File    $importedFile
     *
     * @return Response
     */
    public function confirmAction(Request $request, Event $event, File $importedFile): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->ifEventHasMeetingInterrupImport($event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $spotImportPreviewQuery = new SpotImportPreviewQuery($event, $importedFile, $locale);

        $spotImportViews = $this->get('query.spot.import.spot_import_preview_query_handler')
            ->handle($spotImportPreviewQuery);

        return $this->render('AdminBundle:Spot\Import:spotImportPreview.html.twig', [
            'event' => $event,
            'spotImportViews' => $spotImportViews,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return null|Response
     */
    private function ifEventHasMeetingInterrupImport(Event $event): ?Response
    {
        $hasMeeting = $this->get('vimeet_infrastructure.repository.meeting_repository')->hasMeeting($event);

        if ($hasMeeting) {
            return $this->render('AdminBundle:Spot\Import:spotImportNotAvailable.html.twig', [
                'event' => $event,
            ]);
        }

        return null;
    }
}
