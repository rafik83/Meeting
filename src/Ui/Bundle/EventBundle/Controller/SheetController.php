<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\ButtonLinkDataType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextDataType;

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
        $template      = $sheet->getType()->getNewSheetTemplate();
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

    public function updateAction(Request $request, EventView $eventView, $locale, $key)
    {
        $sheet = $this->getUserSheet($eventView, $locale);

        $factory      = new TemplateDataFactory();
        $templateData = $factory->createFromSheet($sheet, $locale);
        $object       = $templateData->getObject($key);

        $types        = [
            'editable-text' => EditableTextDataType::class,
            'button-link'   => ButtonLinkDataType::class,
        ];

        $form = $this->createForm($types[$object->getType()], $object, [
            'action' => $this->generateUrl('event_sheet_update', ['locale' => $locale, 'key' => $key]),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

        }

        return $this->render('EventBundle:Sheet:update.html.twig', [
            'form'  => $form->createView(),
            'label' => $templateData->getObject($key)->getLabel($locale, $sheet->getEvent()->getFallback()),
        ]);
    }
}
