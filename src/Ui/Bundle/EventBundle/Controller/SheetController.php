<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Sheet\RemoveImage;
use Proximum\Vimeet\Application\Command\Sheet\SubmitValidation;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\SheetValidationViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\TemplateObjectViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\WelcomeViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class SheetController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param null|string $locale
     *
     * @return RedirectResponse
     */
    public function redirectTosheetAction(Request $request, EventDomain $eventDomain, $locale = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        try {
            $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale ?: $request->getLocale());
        } catch (SheetNotFoundException $sheetNotFoundException) {
            throw $this->createAccessDeniedException('User not have Sheet');
        }

        if (null === $locale) {
            return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
        }

        return $this->redirectToRoute('event_sheet_locale', ['sheet' => $sheet->getId(), 'locale' => $locale]);
    }

    /**
     * Display the sheet in the choosen locale (independently from the interface locale).
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     *
     * @return RedirectResponse|Response
     */
    public function sheetAction(Request $request, EventDomain $eventDomain, Sheet $sheet, $locale = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $locale = $locale ?: $request->getLocale();

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

        list ($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheet,
            $this->getUser(),
            $locale
        );

        // Build sheet template data and attach tagged data view to template object with tags
        $templateData = $this->get('template.tagged_data_factory')->buildTaggedDataView($sheet, $locale);

        // todo: remove this to send to add form all participants product
        $participantProductViews = $this->get('tactician.commandbus.query')->handle(
            new ParticipantProductViewQuery($sheet, $locale)
        );

        $participantProductView = reset($participantProductViews);

        if (false === $participantProductView) {
            $participantProductView = null;
        }
        // /todo

        $flagFirstRegistration = $this->container->get('session')->getFlashBag()->get('first_registration');
        $isFirstRegistration   = in_array(true, $flagFirstRegistration);
        $popinWelcome          = null;

        if ($isFirstRegistration) {
            $welcomeViewQuery = new WelcomeViewQuery($sheet);
            $popinWelcome     = $this->get('tactician.commandbus.query')->handle($welcomeViewQuery);
        }

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet->getType(),
            TipTranslationViewQueryHandler::CONTEXT_SHEET,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'event'                   => $eventDomain->getEvent(),
            'sheet'                   => $sheet,
            'taggedData'              => $taggedData,
            'locale'                  => $locale,
            'nomenclatures'           => $nomenclatures,
            'participants'            => $participants,
            'templateData'            => $templateData,
            'popinWelcome'            => $popinWelcome,
            'sheetValidationView'     => (isset($sheetValidationView)) ? $sheetValidationView : null,
            'participantProductView'  => $participantProductView,
            'isRequestMeetingEnabled' => false,
            'isCatalog'               => false,
            'tipTranslationViews'     => $tipTranslationViews,
            'isPhoneValidationRequired' => false
        ]);
    }

    /**
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     * @param int           $sheetToDisplay
     * @param string        $locale
     *
     * @return BinaryFileResponse
     */
    public function generatePdfAction(
        EventDomain $eventDomain,
        Sheet $sheet,
        UserInterface $user,
        $sheetToDisplay,
        $locale
    ) {
        $sheetToDisplay = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetById($sheetToDisplay);

        if (null === $sheetToDisplay || $eventDomain->getEvent() !== $sheetToDisplay->getEvent()) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        if ($sheetToDisplay !== $sheet) {
            if (!$sheetToDisplay->isInCatalog()) {
                throw $this->createAccessDeniedException('Sheet not in catalog');
            }

            $rules = $this
                ->get('repository.rule_repository')
                ->getBySeerTypeAndSeeableType($sheet->getType(), $sheetToDisplay->getType());

            if (empty($rules)) {
                throw $this->createNotFoundException('You do not have the right to see this sheet');
            }
        }

        $pathToPdf = $this->get('printer.sheet_pdf_printer')->generate($user, $sheet, $sheetToDisplay, $locale);

        return new BinaryFileResponse($pathToPdf);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param User        $user
     * @param int         $sheetToDisplay
     * @param string      $locale
     *
     * @return Response
     */
    public function printAction(EventDomain $eventDomain, Sheet $sheet, User $user, $sheetToDisplay, $locale)
    {
        $event  = $eventDomain->getEvent();
        $locale = $event->getAvailableLocale($locale);

        $sheetToDisplay = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetById($sheetToDisplay);

        if (null === $sheetToDisplay || $event !== $sheetToDisplay->getEvent()) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        $isCatalogAllowed = $this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event);

        // Build print template data and attach tagged data view to template object with tags
        $templateData = $this->get('template.tagged_data_factory')->buildTaggedDataViewForPrint(
            $sheetToDisplay,
            $locale
        );

        list ($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheetToDisplay,
            $user,
            $locale
        );

        if ($sheetToDisplay !== $sheet) {
            if (!$sheet->isInCatalog() || !$isCatalogAllowed) {
                throw $this->createAccessDeniedException('Sheet not in catalog');
            }

            $rules = $this
                ->get('repository.rule_repository')
                ->getBySeerTypeAndSeeableType($sheet->getType(), $sheetToDisplay->getType());

            if (empty($rules)) {
                throw $this->createNotFoundException('You do not have the right to see this sheet');
            }

            $ruleApplyer = $this->get('domain.rule.applyer');
            $ruleApplyer->applyRuleForTemplate($templateData, $rules);
            $ruleApplyer->applyRuleForCardList($participants, $rules);
        }

        return $this->render('EventBundle:Sheet:print.html.twig', [
            'event'         => $event,
            'sheet'         => $sheetToDisplay,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
        ]);
    }

    /**
     * Render the form of an object. Loaded by ajax from the sheet.
     *
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     */
    public function formAction(EventDomain $eventDomain, Sheet $sheet, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateObjectView = $this
            ->get('tactician.commandbus')
            ->handle(new TemplateObjectViewQuery($sheet, $locale, $key))
        ;

        $form  = $this->createObjectForm($templateObjectView->templateObject, $locale, $key);

        return $this->render('EventBundle:Sheet:form.html.twig', [
            'sheet'    => $sheet,
            'uid'      => $key,
            'form'     => $form->createView(),
            'locale'   => $locale,
            'currency' => $eventDomain->getEvent()->getCurrency(),
            'vatMode'  => $eventDomain->getEvent()->getMode(),
            'label'    => $templateObjectView->label,
            'templateObjectView' => $templateObjectView
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
            'action' => $this->generateUrl(
                'event_sheet_update',
                ['sheet' => $object->getSheet()->getId(), 'locale' => $locale, 'key' => $key]
            ),
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
     * @param Sheet       $sheet
     * @param string      $locale
     * @param string      $key
     *
     * @return Response
     */
    public function updateAction(Request $request, EventDomain $eventDomain, Sheet $sheet, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData       = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $levelsArchitecture = [];

        try {
            $object = $templateData->getObject($key);
        } catch (Template\Exception\ObjectNotFoundException $exception) {
            throw $this->createNotFoundException(sprintf('The given key %s is not found', $key));
        }


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

        $templateObjectView = $this
            ->get('tactician.commandbus')
            ->handle(new TemplateObjectViewQuery($sheet, $locale, $key))
        ;

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

                return $this->redirectToRoute('event_sheet_locale', ['sheet' => $sheet->getId(), 'locale' => $locale]);
            } else {
                // If the form is not valid, re-render the templateData
                $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
            }
        }

        // If the form is not valid, render the sheet and force the popin with the object form
        list ($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheet,
            $this->getUser(),
            $locale
        );
        $label = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet->getType(),
            TipTranslationViewQueryHandler::CONTEXT_SHEET,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        $twig = $object->getType() === 'nomenclature'
            ? 'EventBundle:Sheet:nomenclatures.html.twig'
            : 'EventBundle:Sheet:sheet.html.twig';

        return $this->render($twig, [
            'event'                   => $eventDomain->getEvent(),
            'form'                    => $form->createView(),
            'label'                   => $label,
            'levelsArchitecture'      => $levelsArchitecture,
            'locale'                  => $locale,
            'nomenclatures'           => $nomenclatures,
            'object'                  => $object,
            'participants'            => $participants,
            'sheet'                   => $sheet,
            'taggedData'              => $taggedData,
            'templateData'            => $templateData,
            'uid'                     => $key,
            'currency'                => $eventDomain->getEvent()->getCurrency(),
            'vatMode'                 => $eventDomain->getEvent()->getMode(),
            'isRequestMeetingEnabled' => false,
            'isCatalog'               => false,
            'tipTranslationViews'     => $tipTranslationViews,
            'templateObjectView'      => $templateObjectView,
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     * @param string      $key
     *
     * @return RedirectResponse
     */
    public function removeImageAction(EventDomain $eventDomain, Sheet $sheet, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $templateData->getObject($key);

        if (!$object instanceof Template\TemplateObject\Image) {
            throw $this->createNotFoundException('The key given is not an image');
        }

        $removeImage = new RemoveImage($object, $sheet, $templateData);
        $this->get('tactician.commandbus')->handle($removeImage);

        return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
    }

    /**
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return RedirectResponse
     */
    public function submitValidationAction(EventDomain $eventDomain, Sheet $sheet, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $submitValidation = new SubmitValidation($sheet, $user);

        $this->get('tactician.commandbus')->handle($submitValidation);

        return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
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
}
