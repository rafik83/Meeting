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
use Proximum\Vimeet\Application\Command\Participant\Remove;
use Proximum\Vimeet\Application\Command\Sheet\RemoveImage;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\RemoveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SheetController extends Controller
{
    /**
     * Display the sheet in the choosen locale (independently from the interface locale).
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param string      $locale
     *
     * @return RedirectResponse|Response
     */
    public function sheetAction(Request $request, EventDomain $eventDomain, $locale = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $locale = $locale ?: $request->getLocale();
        $sheet  = $this->getUserSheet($eventDomain->getEvent(), $locale);

        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $locale
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'sheet'         => $sheet,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
        ]);
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array
     */
    private function sheetInfos(Event $event, Sheet $sheet, $locale)
    {
        $nomenclatures     = $this->get('repository.nomenclature_repository')->findByEvent($event);
        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        $registrationTemplateData = $this
            ->get('template.template_data_factory')
            ->createRegistrationFromSheet($sheet, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return [$nomenclatures, $participants, $taggedData];
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Sheet
     */
    private function getUserSheet(Event $event, $locale)
    {
        $sheets = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetsByUserAndEvent($this->getUser(), $event);

        if (empty($sheets)) {
            throw $this->createNotFoundException('Sheet not found.');
        }

        $sheet = $sheets[array_keys($sheets)[0]];

        if (!$sheet instanceof Sheet) {
            throw $this->createNotFoundException('Sheet not found.');
        }

        if ($sheet->getEvent() !== $event) {
            throw $this->createNotFoundException('Sheet not found');
        }

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createNotFoundException('No participant for this user is attached on this sheet');
        }

        if (!$event->hasLocale($locale)) {
            throw $this->createNotFoundException('Locale not available for this event.');
        }

        return $sheet;
    }

    /**
     * Render the form of an object. Loaded by ajax from the sheet.
     *
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function formAction(EventDomain $eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $templateData->getObject($key);
        $form         = $this->createObjectForm($object, $locale, $key);
        $label        = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());

        return $this->render('EventBundle:Sheet:form.html.twig', [
            'uid'    => $key,
            'label'  => $label,
            'form'   => $form->createView(),
            'object' => $object,
            'locale' => $locale,
        ]);
    }

    /**
     * @param Template\TemplateObject $object
     * @param string                  $locale
     * @param string                  $key
     *
     * @return Form
     */
    private function createObjectForm(Template\TemplateObject $object, $locale, $key)
    {
        $types = [
            'editable-text' => Data\EditableTextDataType::class,
            'button-link'   => Data\ButtonLinkDataType::class,
            'media'         => Data\MediaCollectionDataType::class,
            'collection'    => Data\ItemCollectionDataType::class,
            'nomenclature'  => Data\NomenclatureDataType::class,
            'image'         => Data\ImageDataType::class,
            'tags'          => Data\ItemCollectionDataType::class,
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
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function updateAction(Request $request, EventDomain $eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData       = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object             = $templateData->getObject($key);
        $form               = $this->createObjectForm($object, $locale, $key);
        $levelsArchitecture = [];

        if ($object instanceof Template\TemplateObject\Nomenclature) {
            $nomenclature = $object->getNomenclatureModel();
            $depth        = $nomenclature->getDepth();

            if (2 === $depth || 3 === $depth) {
                $levelsArchitecture = $nomenclature->getLevelsArchitecture();
            }
        }

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted()) {
            if ($form->isValid()) {
                if ($object instanceof Template\TemplateObject\Image) {
                    $file = $form->get('file')->getData();

                    if ($file instanceof UploadedFile) {
                        $image       = $object->getImage();
                        $fileStorage = $this->get('adapter.local_file_storage');

                        if (null !== $image) {
                            $fileStorage->remove($image);
                        }

                        $newImage = $fileStorage->upload($file);
                        $object->setImage($newImage);
                    }
                }

                $this->get('tactician.commandbus')->handle(new UpdateData($sheet, $templateData->getData()));

                return $this->redirectToRoute('event_sheet');
            } else {
                $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
            }
        }

        // If the form is not valid, render the sheet and force the popin with the object form
        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $locale
        );
        $label = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());

        $twig = $object->getType() === 'nomenclature'
            ? 'EventBundle:Sheet:nomenclatures.html.twig'
            : 'EventBundle:Sheet:sheet.html.twig';

        return $this->render($twig, [
            'event'              => $eventDomain->getEvent(),
            'form'               => $form->createView(),
            'label'              => $label,
            'levelsArchitecture' => $levelsArchitecture,
            'locale'             => $locale,
            'nomenclatures'      => $nomenclatures,
            'object'             => $object,
            'participants'       => $participants,
            'sheet'              => $sheet,
            'taggedData'         => $taggedData,
            'templateData'       => $templateData,
            'uid'                => $key,
        ]);
    }

    /**
     * Render the form of the addition of a participant. Loaded by ajax from the sheet.
     *
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function addParticipantAction(EventDomain $eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$sheet->canBuyParticipant()) {
            throw $this->createNotFoundException(
                sprintf('This sheet %s can not buy anymore participant', $sheet->getId())
            );
        }

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if ($object->getType() !== 'participant') {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        $label = $object->getLabel($locale, $sheet->getEvent()->getFallback());

        $addParticipant = new Add($sheet, $locale, $this->getUser());
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
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function handleAddParticipantAction(Request $request, EventDomain $eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$sheet->canBuyParticipant()) {
            throw $this->createNotFoundException(
                sprintf('This sheet %s can not buy anymore participant', $sheet->getId())
            );
        }

        $addParticipant = new Add($sheet, $locale, $this->getUser());
        $form           = $this->createForm(AddType::class, $addParticipant, [
            'action' => $this->generateUrl('event_sheet_handle_participant', [
                'locale' => $locale,
                'key'    => $key
            ]),
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
        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $locale
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $this->getParticipantObject($templateData, $key);
        $label        = $object->getLabel($locale, $sheet->getEvent()->getFallback());

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'            => $eventDomain->getEvent(),
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

    /**
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @throws \Exception
     * @return array
     */
    private function removeParticipantData($eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if ($sheet->countParticipants() === 1) {
            throw $this->createNotFoundException('Impossible to remove participants from a sheet with one participant');
        }

        $remove = new Remove($sheet);
        $form   = $this->createForm(RemoveType::class, $remove, [
            'action'       => $this->generateUrl('event_sheet_handle_remove_participant', ['locale' => $locale, 'key'    => $key,]),
            'participants' => $sheet->getParticipants(),
        ]);

        return [
            $form,
            $sheet,
            $remove,
        ];
    }

    /**
     * Render the form to remove participant. Loaded by ajax from the sheet.
     *
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function removeParticipantAction(EventDomain $eventDomain, $locale, $key)
    {
        list ($form, $sheet) = $this->removeParticipantData($eventDomain, $locale, $key);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if ($object->getType() !== 'participant') {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        $label             = $object->getLabel($locale, $sheet->getEvent()->getFallback());
        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale, false);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        return $this->render('EventBundle:Participant:remove.html.twig', [
            'uid'          => $key,
            'form'         => $form->createView(),
            'label'        => $label,
            'participants' => $participants,
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return RedirectResponse
     */
    public function removeImageAction(EventDomain $eventDomain, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale);
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $templateData->getObject($key);

        $removeImage = new RemoveImage($object, $sheet, $templateData);
        $this->get('tactician.commandbus')->handle($removeImage);

        return $this->redirectToRoute('event_sheet');
    }

    /**
     * Remove a participant and display the sheet with the modal in case of form error.
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     * @throws \Exception
     */
    public function handleRemoveParticipantAction(Request $request, EventDomain $eventDomain, $locale, $key)
    {
        list ($form, $sheet, $remove) = $this->removeParticipantData($eventDomain, $locale, $key);

        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        // Handle the form, update the object and redirect to the sheet if valid
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($remove);

                return $this->redirectToRoute('event_sheet_locale', ['locale' => $locale]);
            } catch (CanNotRemoveAllParticipantsException $exception) {
                $form->addError(new FormError('validators.participant.canNotRemoveAllParticipants'));
            }
        }

        // If the form is not valid, render the sheet and force the popin with the remove participant form
        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $locale
        );
        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $this->getParticipantObject($templateData, $key);
        $label        = $object->getLabel($locale, $sheet->getEvent()->getFallback());

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'         => $eventDomain->getEvent(),
            'sheet'         => $sheet,
            'templateData'  => $templateData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'taggedData'    => $taggedData,
            'form_remove'   => $form->createView(),
            'label'         => $label,
            'uid'           => $key,
            'participants'  => $participants,
        ]);
    }

    /**
     * @param Template\TemplateData $templateData
     * @param string                $key
     *
     * @return Template\TemplateObject
     */
    private function getParticipantObject(Template\TemplateData $templateData, $key)
    {
        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }

        if ($object->getType() !== 'participant') {
            throw $this->createNotFoundException(sprintf('The given object %s is not a participant', $key));
        }

        return $object;
    }
}
