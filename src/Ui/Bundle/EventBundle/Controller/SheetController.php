<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Proximum\Vimeet\Domain\Template\Object;

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
        $template      = $sheet->getType()->getSheetTemplate();
        $data          = $sheet->getData();
        $nomenclatures = $this->get('repository.nomenclature_repository')->findByEvent($eventView->getId());

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'eventView'     => $eventView,
            'sheet'         => $sheet,
            'template'      => $template,
            'data'          => $data,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
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
            throw $this->createAccessDeniedException('No participant for this user is attached on this sheet');
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
     * @param Object $object
     * @param string $locale
     * @param string $key
     *
     * @return Form
     */
    private function createObjectForm(Object $object, $locale, $key)
    {
        $types = [
            'editable-text' => Data\EditableTextDataType::class,
            'button-link'   => Data\ButtonLinkDataType::class,
            'media'         => Data\MediaCollectionDataType::class,
            'collection'    => Data\ItemCollectionDataType::class,
            'nomenclature'  => Data\NomenclatureDataType::class,
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
            $this->get('tactician.commandbus')->handle(new UpdateData($sheet, $templateData->getData()));

            return $this->redirectToRoute('event_sheet_locale', ['locale' => $locale]);
        }

        // If the form is not valid, render the sheet and force the popin with the object form
        $nomenclatures = $this->get('repository.nomenclature_repository')->findByEvent($eventView->getId());
        $template      = $sheet->getType()->getSheetTemplate();
        $data          = $sheet->getData();
        $label         = $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback());

        $twig = $object->getType() === 'nomenclature'
            ? 'EventBundle:Sheet:nomenclatures.html.twig'
            : 'EventBundle:Sheet:sheet.html.twig';

        return $this->render($twig, [
            'eventView'     => $eventView,
            'sheet'         => $sheet,
            'template'      => $template,
            'data'          => $data,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'form'          => $form->createView(),
            'label'         => $label,
            'uid'           => $key,
        ]);
    }
}
