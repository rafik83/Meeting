<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Spot;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Spot\Import\SpotImport;
use Proximum\Vimeet\Application\Command\Spot\Import\SpotImportConfirm;
use Proximum\Vimeet\Application\Query\Spot\Import\SpotImportPreviewQuery;
use Proximum\Vimeet\Application\Query\Spot\Import\SpotImportPreviewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\Import\SpotConfirmType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot\Import\SpotImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends AbstractController
{
    private MeetingRepositoryInterface $meetingRepository;
    private SpotImportPreviewQueryHandler $spotImportPreviewQueryHandler;
    private CommandBusInterface $commandBus;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        SpotImportPreviewQueryHandler $spotImportPreviewQueryHandler,
        CommandBusInterface $commandBus
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->spotImportPreviewQueryHandler = $spotImportPreviewQueryHandler;
        $this->commandBus = $commandBus;
    }

    public function importAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $hasMeeting = $this->meetingRepository->hasMeeting($event);

        if ($hasMeeting) {
            return $this->render('AdminBundle:Spot\Import:spotImportNotAvailable.html.twig', ['event' => $event]);
        }

        $spotImport = new SpotImport();

        $form = $this->createForm(SpotImportType::class, $spotImport, ['submit' => true]);

        if ($form->handleRequest($request)->isValid() && $form->isSubmitted()) {
            /** @var File $importedFile */
            $importedFile = $this->commandBus->handle($spotImport);

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

    public function confirmAction(Request $request, Event $event, File $importedFile): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $hasMeeting = $this->meetingRepository->hasMeeting($event);

        if ($hasMeeting) {
            return $this->render('AdminBundle:Spot\Import:spotImportNotAvailable.html.twig', ['event' => $event]);
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        $spotImportPreviewQuery = new SpotImportPreviewQuery($event, $importedFile, $locale);

        $spotImports = $this->spotImportPreviewQueryHandler->handle($spotImportPreviewQuery);

        $spotImportConfirm = new SpotImportConfirm($event, $importedFile, $locale);

        $form = $this->createForm(SpotConfirmType::class, $spotImportConfirm, ['locale' => $locale]);

        if ($form->handleRequest($request)->isValid() && $form->isSubmitted()) {
            $this->commandBus->handle($spotImportConfirm);

            return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Spot\Import:spotImportPreview.html.twig', [
            'event' => $event,
            'spotImports' => $spotImports,
            'form' => $form->createView(),
        ]);
    }

    public function getSampleAction(Event $event): CsvFileResponse
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
