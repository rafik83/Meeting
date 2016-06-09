<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Proximum\Vimeet\Domain\Template;

class SheetController extends Controller
{
    /**
     * Display the sheet in the choosen locale (independently from the interface locale).
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param string    $locale
     *
     * @return RedirectResponse|Response
     */
    public function sheetAction(Request $request, EventView $eventView, $locale = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $locale        = $locale ? : $request->getLocale();
        $sheet         = $this->getUserSheet($eventView, $locale);
        $nomenclatures = $this->get('repository.nomenclature_repository')->findByEvent($eventView->getId());
        $participants  = $this->get('tactician.commandbus.query')->handle(
            new CardListViewQuery(
                $sheet,
                $this->getUser(),
                $locale
            )
        );

        $registrationTemplateData = $this
            ->get('template.template_data_factory')
            ->createRegistrationFromSheet($sheet, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'eventView'     => $eventView,
            'sheet'         => $sheet,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
        ]);
    }

    /**
     * @param EventView $eventView
     * @param string    $locale
     *
     * @return Sheet
     */
    private function getUserSheet(EventView $eventView, $locale)
    {
        $sheets = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetByUserAndEvent($this->getUser(), $eventView);

        if (empty($sheets)) {
            throw $this->createNotFoundException('Sheet not found.');
        }

        $sheet = $sheets[array_keys($sheets)[0]];

        if (!$sheet instanceof Sheet) {
            throw $this->createNotFoundException('Sheet not found.');
        }

        if ($sheet->getEvent()->getId() !== $eventView->getId()) {
            throw $this->createNotFoundException('Sheet not found');
        }

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createNotFoundException('No participant for this user is attached on this sheet');
        }

        if (!$eventView->hasLocale($locale)) {
            throw $this->createNotFoundException('Locale not available for this event.');
        }

        return $sheet;
    }

    /**
     * Render the form of an object. Loaded by ajax from the sheet.
     *
     * @param EventView $eventView
     * @param string    $locale
     * @param string    $key
     *
     * @return Response
     * @throws \Exception
     */
    public function formAction(EventView $eventView, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheet        = $this->getUserSheet($eventView, $locale);
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $templateData->getObject($key);
        $form         = $this->createObjectForm($object, $locale, $key);
        $label        = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());

        return $this->render('EventBundle:Sheet:form.html.twig', [
            'uid'   => $key,
            'form'  => $form->createView(),
            'label' => $label,
            'type'  => $object->getType(),
        ]);
    }

    /**
     * @param Template\Object $object
     * @param string          $locale
     * @param string          $key
     *
     * @return Form
     */
    private function createObjectForm(Template\Object $object, $locale, $key)
    {
        $types = [
            'editable-text' => Data\EditableTextDataType::class,
            'button-link'   => Data\ButtonLinkDataType::class,
            'media'         => Data\MediaCollectionDataType::class,
            'collection'    => Data\ItemCollectionDataType::class,
            'nomenclature'  => Data\NomenclatureDataType::class,
            'image'         => Data\ImageDataType::class,
        ];

        if (!isset($types[$object->getType()])) {
            throw $this->createNotFoundException('No form found for this object');
        }


        return $this->createForm($types[$object->getType()], $object, [
            'action'      => $this->generateUrl('event_sheet_update', ['locale' => $locale, 'key' => $key]),
            'submit'      => true,
            'locale'      => $locale,
            'help'        => $object->getHelp(),
            'placeholder' => $object->getPlaceholder(),
            'object'      => $object,
            'required'    => $object->getRequired(),
        ]);
    }

    /**
     * Update an object and display the sheet with the modal in case of form error.
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param string    $locale
     * @param string    $key
     *
     * @return Response
     * @throws \Exception
     */
    public function updateAction(Request $request, EventView $eventView, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheet        = $this->getUserSheet($eventView, $locale);
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $templateData->getObject($key);
        $form         = $this->createObjectForm($object, $locale, $key);

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            if ($object instanceof Template\Object\Image) {
                $file = $form->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $image        = $object->getImage();
                    $fileStorage  = $this->get('adapter.local_file_storage');

                    if (null !== $image) {
                        $fileStorage->remove($image);
                    }

                    $newImage = $fileStorage->upload($file);
                    $object->setImage($newImage);
                }
            }

            $this->get('tactician.commandbus')->handle(new UpdateData($sheet, $templateData->getData()));

            return $this->redirectToRoute('event_sheet_locale', ['locale' => $locale]);
        }

        // If the form is not valid, render the sheet and force the popin with the object form
        $nomenclatures = $this->get('repository.nomenclature_repository')->findByEvent($eventView->getId());
        $label         = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());
        $participants  = $this->get('tactician.commandbus.query')->handle(
            new CardListViewQuery(
                $sheet,
                $this->getUser(),
                $locale
            )
        );

        $registrationTemplateData = $this
            ->get('template.template_data_factory')
            ->createRegistrationFromSheet($sheet, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        $twig = $object->getType() === 'nomenclature'
            ? 'EventBundle:Sheet:nomenclatures.html.twig'
            : 'EventBundle:Sheet:sheet.html.twig';

        return $this->render($twig, [
            'eventView'     => $eventView,
            'sheet'         => $sheet,
            'templateData'  => $templateData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'taggedData'    => $taggedData,
            'form'          => $form->createView(),
            'label'         => $label,
            'uid'           => $key,
            'participants'  => $participants,
        ]);
    }

    /**
     * Render the form of the addition of a participant. Loaded by ajax from the sheet.
     *
     * @param EventView $eventView
     * @param string    $locale
     * @param string    $key
     *
     * @return Response
     * @throws \Exception
     */
    public function addParticipantAction(EventView $eventView, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheet        = $this->getUserSheet($eventView, $locale);
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $label        = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());

        $addParticipant = new Add($sheet, $eventView, $locale);
        $form           = $this->createForm(AddType::class, $addParticipant, [
            'action' => $this->generateUrl('event_sheet_handle_participant', ['locale' => $locale, 'key' => $key]),
        ]);

        return $this->render('EventBundle:Participant:add.html.twig', [
            'uid'   => $key,
            'form'  => $form->createView(),
            'label' => $label,
        ]);
    }

    /**
     * Add a participant and display the sheet with the modal in case of form error.
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param string    $locale
     * @param string    $key
     *
     * @return Response
     * @throws \Exception
     */
    public function handleAddParticipantAction(Request $request, EventView $eventView, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheet        = $this->getUserSheet($eventView, $locale);
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $label        = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());

        $addParticipant = new Add($sheet, $eventView, $locale);
        $form           = $this->createForm(AddType::class, $addParticipant, [
            'action' => $this->generateUrl('event_sheet_handle_participant', ['locale' => $locale, 'key' => $key]),
        ]);

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($addParticipant);

                return $this->redirectToRoute('event_sheet_locale', ['locale' => $locale]);
            } catch (AlreadyLinkedToASheetOfThisEventException $exception) {
                $form->get('email')->addError(new FormError('validators.participant.alreadyLinkedToASheet'));
            } catch (ParticipantAlreadyExistException $exception) {
                $form->get('email')->addError(new FormError('validators.participant.alreadyLinkedToThisSheet'));
            }
        }

        // If the form is not valid, render the sheet and force the popin with the participant form
        $nomenclatures = $this->get('repository.nomenclature_repository')->findByEvent($eventView->getId());
        $participants  = $this->get('tactician.commandbus.query')->handle(
            new CardListViewQuery(
                $sheet,
                $this->getUser(),
                $locale
            )
        );

        $registrationTemplateData = $this
            ->get('template.template_data_factory')
            ->createRegistrationFromSheet($sheet, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'eventView'        => $eventView,
            'sheet'            => $sheet,
            'templateData'     => $templateData,
            'locale'           => $locale,
            'nomenclatures'    => $nomenclatures,
            'taggedData'       => $taggedData,
            'form_participant' => $form->createView(),
            'label'            => $label,
            'uid'              => $key,
            'participants'     => $participants,
        ]);
    }
}
