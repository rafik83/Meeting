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
use Proximum\Vimeet\Application\Command\Sheet\SubmitValidation;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\SheetValidationViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\RemoveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

        $participantRepository = $this->get('vimeet_infrastructure.repository.participant_repository');
        $participant           = $participantRepository->getParticipantForUserAndSheet($this->getUser(), $sheet);

        if (null !== $participant) {
            $registrationStepManager = $this->get('components.registration.step_manager');
            $redirectStep            = $registrationStepManager->getRedirectStep($sheet, $participant);

            if (true === $redirectStep['redirect']) {
                return $this->redirectToRoute($redirectStep['route'], $redirectStep['parameters']);
            }
        }

        if ($sheet->isValidationDraft()) {
            $sheetValidationView = $this->get('tactician.commandbus.query')->handle(
                new SheetValidationViewQuery(
                    $sheet,
                    $locale
                )
            );
        }

        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $this->getUser(),
            $locale
        );
        $templateData       = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $participantProduct = $sheet->getPackage()->isPassable() ? $sheet->getPackage()->getParticipant() : null;

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'               => $eventDomain->getEvent(),
            'sheet'               => $sheet,
            'taggedData'          => $taggedData,
            'locale'              => $locale,
            'nomenclatures'       => $nomenclatures,
            'participants'        => $participants,
            'templateData'        => $templateData,
            'sheetValidationView' => (isset($sheetValidationView)) ? $sheetValidationView : null,
            'participantProduct'  => $participantProduct,
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     *
     * @return BinaryFileResponse
     */
    public function generatePdfAction(EventDomain $eventDomain, Sheet $sheet, $locale)
    {
        $user      = $this->getUser();
        $userSheet = $this->get('sheet.sheet_guesser')->getUserSheet($user, $eventDomain->getEvent(), $locale);

        if ($userSheet !== $sheet) {
            if (!$sheet->isInCatalog()) {
                throw $this->createAccessDeniedException('Sheet not in catalog');
            }

            $rules = $this
                ->get('repository.rule_repository')
                ->getBySeerTypeAndSeeableType($userSheet->getType(), $sheet->getType());

            if (empty($rules)) {
                throw $this->createNotFoundException('You do not have the right to see this sheet');
            }
        }

        $pathToPdf = $this->get('printer.sheet_pdf_printer')->generate($this->getUser(), $sheet, $locale);

        return new BinaryFileResponse($pathToPdf);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param User        $user
     * @param string      $locale
     *
     * @return Response
     */
    public function printAction(EventDomain $eventDomain, Sheet $sheet, User $user, $locale)
    {
        $event     = $eventDomain->getEvent();
        $locale    = $event->getAvailableLocale($locale);
        $userSheet = $this->get('sheet.sheet_guesser')->getUserSheet($user, $event, $locale);

        $isCatalogAllowed = $this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event);

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);

        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $event,
            $sheet,
            $user,
            $locale
        );

        if ($userSheet !== $sheet) {
            if (!$sheet->isInCatalog() || !$isCatalogAllowed) {
                throw $this->createAccessDeniedException('Sheet not in catalog');
            }

            $rules = $this
                ->get('repository.rule_repository')
                ->getBySeerTypeAndSeeableType($userSheet->getType(), $sheet->getType());

            if (empty($rules)) {
                throw $this->createNotFoundException('You do not have the right to see this sheet');
            }

            $ruleApplyer = $this->get('domain.rule.applyer');
            $ruleApplyer->applyRuleForTemplate($templateData, $rules);
            $ruleApplyer->applyRuleForCardList($participants, $rules);
        }

        return $this->render('EventBundle:Sheet:print.html.twig', [
            'event'         => $event,
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
     * @param User   $fromUser
     * @param string $locale
     *
     * @return array
     */
    private function sheetInfos(Event $event, Sheet $sheet, User $fromUser, $locale)
    {
        $nomenclatures     = $this->get('repository.nomenclature_repository')->findByEvent($event);
        $cardListViewQuery = new CardListViewQuery($sheet, $fromUser, $locale);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        $registrationTemplateData = $this
            ->get('template.template_data_factory')
            ->createRegistrationFromSheet($sheet, $locale);

        $taggedData = $registrationTemplateData->getAllTaggedDatas();

        return [$nomenclatures, $participants, $taggedData];
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

        $products = $this->get('package.product.template_product_guesser')->getProducts(
            $object,
            $sheet->getPackage()
        );

        // populate object needed variables
        $object->setBuyableProducts($products);
        $object->setSheet($sheet);

        $form  = $this->createObjectForm($object, $locale, $key);
        $label = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());

        return $this->render('EventBundle:Sheet:form.html.twig', [
            'uid'      => $key,
            'label'    => $label,
            'form'     => $form->createView(),
            'object'   => $object,
            'locale'   => $locale,
            'currency' => $eventDomain->getEvent()->getCurrency(),
            'vatMode'  => $eventDomain->getEvent()->getMode(),
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
        $levelsArchitecture = [];

        if ($object instanceof Template\TemplateObject\Nomenclature) {
            $nomenclature = $object->getNomenclatureModel();
            $depth        = $nomenclature->getDepth();

            if (2 === $depth || 3 === $depth) {
                $levelsArchitecture = $nomenclature->getLevelsArchitecture($locale);
            }
        }

        $products = $this->get('package.product.template_product_guesser')->getProducts(
            $object,
            $sheet->getPackage()
        );

        $object->setBuyableProducts($products);
        $object->setSheet($sheet);

        $form = $this->createObjectForm($object, $locale, $key);

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

                $this->get('tactician.commandbus')->handle(new UpdateData($sheet, $templateData, $object));

                return $this->redirectToRoute('event_sheet_locale', ['locale' => $locale]);
            } else {
                // If the form is not valid, re-render the templateData
                $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
            }
        }

        // If the form is not valid, render the sheet and force the popin with the object form
        list ($nomenclatures, $participants, $taggedData) = $this->sheetInfos(
            $eventDomain->getEvent(),
            $sheet,
            $this->getUser(),
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
            'currency'           => $eventDomain->getEvent()->getCurrency(),
            'vatMode'            => $eventDomain->getEvent()->getMode(),
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

        $participantProduct = $sheet->getPackage()->isPassable() ? $sheet->getPackage()->getParticipant() : null;

        return $this->render('EventBundle:Participant:add.html.twig', [
            'uid'                => $key,
            'form'               => $form->createView(),
            'sheet'              => $sheet,
            'label'              => $label,
            'participantProduct' => $participantProduct,
            'backRoute'          => 'backToSheet'
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
            $this->getUser(),
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
            'action' => $this->generateUrl(
                'event_sheet_handle_remove_participant',
                ['locale' => $locale, 'key' => $key]
            ),
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
            $this->getUser(),
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
     * @param Sheet $sheet
     *
     * @return RedirectResponse
     */
    public function submitValidationAction(Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $submitValidation = new SubmitValidation($sheet, $this->getUser());

        $this->get('tactician.commandbus')->handle($submitValidation);

        return $this->redirectToRoute('event_sheet');
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Sheet
     */
    private function getUserSheet(Event $event, $locale)
    {
        return $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $locale);
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
