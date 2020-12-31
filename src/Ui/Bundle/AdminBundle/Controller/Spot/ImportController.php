<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Spot;

use Proximum\Vimeet\Application\Command\Spot\Import\SpotImport;
use Proximum\Vimeet\Application\Command\Spot\Import\SpotImportConfirm;
use Proximum\Vimeet\Application\Query\Spot\Import\SpotImportPreviewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\Import\SpotConfirmType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\Import\SpotImportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $hasMeeting = $this->get('vimeet_infrastructure.repository.meeting_repository')->hasMeeting($event);

        if ($hasMeeting) {
            return $this->render('AdminBundle:Spot\Import:spotImportNotAvailable.html.twig', ['event' => $event]);
        }

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

        $hasMeeting = $this->get('vimeet_infrastructure.repository.meeting_repository')->hasMeeting($event);

        if ($hasMeeting) {
            return $this->render('AdminBundle:Spot\Import:spotImportNotAvailable.html.twig', ['event' => $event]);
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        $spotImportPreviewQuery = new SpotImportPreviewQuery($event, $importedFile, $locale);

        $spotImports = $this->get('query.spot.import.spot_import_preview_query_handler')
            ->handle($spotImportPreviewQuery);

        $spotImportConfirm = new SpotImportConfirm($event, $importedFile, $locale);

        $form = $this->createForm(SpotConfirmType::class, $spotImportConfirm, ['locale' => $locale]);

        if ($form->handleRequest($request)->isValid() && $form->isSubmitted()) {
            $this->get('tactician.commandbus')->handle($spotImportConfirm);

            return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Spot\Import:spotImportPreview.html.twig', [
            'event' => $event,
            'spotImports' => $spotImports,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Event $event
     *
     * @return CsvFileResponse
     */
    public function getSampleAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $sample = 'reference;size;meetingCapacity;seatCapacity;sheets;priority;active;visio
A1;10;2;4;;1;1;0
A2;4;3;10;;2;1;1
A3;3;1;4;;2;0;0
A4;20;2;4;1371;2;1;1
A5;10;3;5;2114,7392;2;1;1';

        return new CsvFileResponse($sample, 'spot-import-sample.csv');
    }
}
