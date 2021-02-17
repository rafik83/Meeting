<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Behat\Transliterator\Transliterator;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Nomenclature\Assign;
use Proximum\Vimeet\Application\Command\Nomenclature\AssignResult;
use Proximum\Vimeet\Application\Command\Nomenclature\Create;
use Proximum\Vimeet\Application\Command\Nomenclature\CreateResult;
use Proximum\Vimeet\Application\Command\Nomenclature\Exception\MissingKeysException;
use Proximum\Vimeet\Application\Command\Nomenclature\Import;
use Proximum\Vimeet\Application\Command\Nomenclature\Update;
use Proximum\Vimeet\Application\Nomenclature\Export\CsvExporter;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\DepthException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\ImportException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\InvalidLocaleException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\LocalesMustCorrespondToThoseOfTheEventException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\NoLocaleSpecifiedException;
use Proximum\Vimeet\Application\Query\Nomenclature\EventNomenclatureViewQuery;
use Proximum\Vimeet\Application\Query\Nomenclature\GlobalNomenclatureViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Data\Nomenclature\ExportData;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Data\Nomenclature\ImportData;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\AssignType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\ExportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\ImportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class NomenclatureController extends AbstractController
{
    private ErrorFactory $errorFactory;
    private FormFactoryInterface $formFactory;
    private CsvExporter $nomenclatureCsvExporter;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        ErrorFactory $errorFactory,
        FormFactoryInterface $formFactory,
        CsvExporter $nomenclatureCsvExporter,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->errorFactory = $errorFactory;
        $this->formFactory = $formFactory;
        $this->nomenclatureCsvExporter = $nomenclatureCsvExporter;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    /**
     * List globals nomenclatures
     */
    public function globalsAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $command = new Create();
        $form    = $this->createForm(CreateType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

            /** @var CreateResult $result */
            $result = $this->commandBus->handle($command);

            return $this->redirectToRoute('admin_nomenclature_read', ['nomenclature' => $result->nomenclature->getId()]);
        }

        $nomenclatures = $this->queryBus->handle(new GlobalNomenclatureViewQuery());

        return $this->render('AdminBundle:Nomenclature:globals.html.twig', [
            'form'          => $form->createView(),
            'nomenclatures' => $nomenclatures,
        ]);
    }

    /**
     * List event nomenclature
     */
    public function eventAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Create($event);
        $form    = $this->createForm(CreateType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            /** @var CreateResult $result */
            $result = $this->commandBus->handle($command);

            return $this->redirect($this->getReadNomenclatureUrl($result->nomenclature));
        }

        $nomenclatures = $this->queryBus->handle(new EventNomenclatureViewQuery($event));

        return $this->render('AdminBundle:Nomenclature:event.html.twig', [
            'event'         => $event,
            'form'          => $form->createView(),
            'nomenclatures' => $nomenclatures,
        ]);
    }

    public function readAction(Request $request, Nomenclature $nomenclature): Response
    {
        return $this->read($request, $nomenclature);
    }

    public function eventReadAction(Request $request, Event $event, Nomenclature $nomenclature): Response
    {
        return $this->read($request, $nomenclature, $event);
    }

    /**
     * Display the Nomenclature, update form and import/export feature
     */
    private function read(Request $request, Nomenclature $nomenclature, Event $event = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        if ($nomenclature->getEvent()) {
            $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $nomenclature->getEvent());
        }

        // Handle update
        $update     = new Update($nomenclature);
        $updateForm = $this->createForm(UpdateType::class, $update, ['submit' => true]);

        if ($url = $this->handleUpdate($request, $updateForm, $update)) {
            return $this->redirect($url);
        }

        // Handle import
        $import     = new ImportData($nomenclature);
        $importForm = $this->createForm(ImportType::class, $import);

        if ($url = $this->handleImport($request, $importForm, $import)) {
            return $this->redirect($url);
        }

        // Handle assign
        $assign     = new Assign($nomenclature);
        $assignForm = $this->createForm(AssignType::class, $assign, ['submit' => true, 'admin' => $this->getUser()]);

        if ($url = $this->handleAssign($request, $assignForm, $assign)) {
            return $this->redirect($url);
        }

        // Handle export
        $export     = new ExportData($nomenclature);
        $exportForm = $this->createExportForm($export);

        $eventParam = [];

        if (null !== $event) {
            $eventParam = ['event' => $event];
        }

        return $this->render('AdminBundle:Nomenclature:read.html.twig', array_merge([
            'update_form'  => $updateForm->createView(),
            'import_form'  => $importForm->createView(),
            'assign_form'  => $assignForm->createView(),
            'export_form'  => $exportForm->createView(),
            'nomenclature' => $nomenclature,
        ], $eventParam));
    }

    /**
     * Handle Nomenclature update
     */
    private function handleUpdate(Request $request, FormInterface $form, Update $data): ?string
    {
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            /** @var Nomenclature $nomenclature */
            $nomenclature = $data->nomenclature;

            $this->denyAccessUnlessNomenclatureAccess($nomenclature);

            $this->commandBus->handle($data);
            $this->addFlash('success', 'flash.admin.nomenclature.update.success');

            return $this->getReadNomenclatureUrl($nomenclature);
        }

        return null;
    }

    /**
     * Handle csv import of a Nomenclature
     */
    private function handleImport(Request $request, FormInterface $form, ImportData $data): ?string
    {
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessNomenclatureAccess($data->nomenclature);

            try {
                $import = new Import(
                    $data->nomenclature,
                    $data->file ? $data->file->getPathname() : null,
                    $data->charset
                );

                $this->commandBus->handle($import);
                $this->addFlash('success', 'flash.admin.nomenclature.import.success');

                return $this->getReadNomenclatureUrl($data->nomenclature);
            } catch (DepthException $depthException) {
                $form->addError($this->errorFactory->create('validators.nomenclature.import.depthError'));
            } catch (NoLocaleSpecifiedException $noLocaleSpecifiedException) {
                $form->addError($this->errorFactory->create('validators.nomenclature.import.noLocaleSpecified'));
            } catch (InvalidLocaleException $invalidLocaleException) {
                $form->addError($this->errorFactory->create(
                    'validators.nomenclature.import.invalidLocale',
                    null,
                    'validators',
                    ['%invalidLocale%' => $invalidLocaleException->getInvalidLocale()]
                ));
            } catch (LocalesMustCorrespondToThoseOfTheEventException $localesMustCorrespondToThoseOfTheEventException) {
                $form->addError($this->errorFactory->create(
                    'validators.nomenclature.import.localesMustCorrespondToThoseOfTheEventException'
                ));
            } catch (MissingKeysException $exception) {
                $form->addError($this->errorFactory->create('validators.nomenclature.import.missing_keys'));
            } catch (ImportException $exception) {
                $form->addError($this->errorFactory->create('validators.nomenclature.import.error'));
            }
        }

        return null;
    }

    /**
     * Assign a nomenclature to an event
     */
    private function handleAssign(Request $request, FormInterface $form, Assign $data): ?string
    {
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $data->event);

            /** @var AssignResult $result */
            $result = $this->commandBus->handle($data);
            $this->addFlash('success', 'flash.admin.nomenclature.assign.success');

            return $this->getReadNomenclatureUrl($result->nomenclature);
        }

        return null;
    }

    /**
     * Exports Nomenclature data to csv
     */
    public function exportAction(Request $request, Nomenclature $nomenclature): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $export     = new ExportData($nomenclature);
        $exportForm = $this->createExportForm($export);

        if ($exportForm->handleRequest($request)->isSubmitted() && $exportForm->isValid()) {
            $filepath = sys_get_temp_dir() . '/nomenclature_' . uniqid();
            $file     = $this->nomenclatureCsvExporter->export($nomenclature, $filepath, $export->charset);
            $filename = sprintf('nomenclature-%s.csv', Transliterator::urlize($nomenclature->getTitle()));
            $response = new BinaryFileResponse($file);
            $response->setCharset($export->charset);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

            return $response;
        }

        throw $this->createNotFoundException();
    }

    /**
     * @param Nomenclature $nomenclature
     */
    private function denyAccessUnlessNomenclatureAccess(Nomenclature $nomenclature)
    {
        if ($nomenclature->getEvent()) {
            $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $nomenclature->getEvent());
        } else {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        }
    }

    private function createExportForm(ExportData $export): FormInterface
    {
        return $this->formFactory->createNamed('', ExportType::class, $export, [
            'action' => $this->generateUrl('admin_nomenclature_export', ['nomenclature' => $export->nomenclature->getId()]),
        ]);
    }

    /**
     * @param Nomenclature $nomenclature
     *
     * @return string
     */
    private function getReadNomenclatureUrl(Nomenclature $nomenclature)
    {
        if ($nomenclature->getEvent()) {
            return $this->generateUrl('admin_nomenclature_event_read', [
                'event'        => $nomenclature->getEvent()->getId(),
                'nomenclature' => $nomenclature->getId(),
            ]);
        }

        return $this->generateUrl('admin_nomenclature_read', ['nomenclature' => $nomenclature->getId()]);
    }
}
