<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Sheet\UpdateBlock;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Query\Sheet\SheetPreviewQuery;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\UpdateBlockType;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SheetController extends Controller
{
    public function indexAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $nomenclatures = $this->get('repository.nomenclature_repository')->findByEvent($eventView->getId());

        return $this->render('EventBundle:Sheet:index.html.twig', [
            'eventView'     => $eventView,
            'sheet'         => $sheet,
            'template'      => $sheet->getType()->getNewSheetTemplate(),
            'data'          => $sheet->getData(),
            'locale'        => $request->getLocale(),
            'nomenclatures' => $nomenclatures,
        ]);
    }

    /**
     * Display the sheet in the choosen locale (independently from the interface locale).
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param string    $locale
     *
     * @return RedirectResponse|Response
     */
    public function sheetAction(Request $request, EventView $eventView, Sheet $sheet, $locale = null)
    {
        // We must refresh sheet to make behat feature working ...
        $this->getDoctrine()->getManager()->refresh($sheet);

        $locale = $locale ? : $request->getLocale();

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('No participant for this user attached on this sheet');
        }

        if (!$eventView->hasLocale($locale)) {
            throw $this->createNotFoundException('Locale not available for this event.');
        }

        $preview = $this->get('tactician.commandbus.query')->handle(new SheetPreviewQuery($sheet, $this->getUser(), $locale));

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'sheet'         => $sheet,
            'eventView'     => $eventView,
            'preview'       => $preview,
        ]);
    }

    /**
     * Update sheet part in the choosen locale (independently from the interface locale)
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param string    $locale
     * @param int       $block
     *
     * @return Response
     */
    public function blockAction(Request $request, EventView $eventView, Sheet $sheet, $locale, $block)
    {
        // We must refresh sheet to make behat feature working ...
        $this->getDoctrine()->getManager()->refresh($sheet);

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$eventView->hasLocale($locale)) {
            throw $this->createNotFoundException('This locale is not available.');
        }

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('No participant for this user attached on this sheet');
        }

        $sheetTemplate = $sheet->getType()->getSheetTemplate();

        if (!isset($sheetTemplate[$block])) {
            throw $this->createNotFoundException('Block not found.');
        }

        $updateBlock = new UpdateBlock($sheet, $block, $locale);
        $form        = $this->createForm(UpdateBlockType::class, $updateBlock, [
            'template' => $sheetTemplate[$block]['template'],
            'locale'   => $locale,
            'submit'   => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($updateBlock);
                $this->addFlash('success', 'flash.sheet.update_block.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet_locale', ['sheet' => $sheet->getId(), 'locale' => $locale]);
            } catch (RequiredDataEmptyException $exception) {
                foreach ($exception->getKeys() as $key) {
                    $form->get($key)->addError(new FormError('validators.field.required'));
                }
            }
        }

        return $this->render('EventBundle:Sheet:block.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }
}
