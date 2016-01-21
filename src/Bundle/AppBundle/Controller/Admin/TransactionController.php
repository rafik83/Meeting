<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Transaction\Create;
use Proximum\Vimeet\Application\Command\Transaction\Remove;
use Proximum\Vimeet\Application\Command\Transaction\Update;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Transaction\CreateTransactionType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Transaction\UpdateTransactionType;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    /**
     * @param Request $request
     * @param Sheet   $sheet
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Sheet $sheet)
    {
        $create = new Create($sheet, null, new \DateTime());
        $form   = $this->createForm(CreateTransactionType::class, $create);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.transaction.create_handler')->handle($create);
            $this->addFlash('success', 'flash.admin.transaction.create.success');

            return $this->redirectToRoute('admin_sheet_billing', [
                'id'       => $sheet->getEvent()->getId(),
                'sheet_id' => $sheet->getId(),
            ]);
        }

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($sheet);

        return $this->render('VimeetAppBundle:Admin/Transaction:create.html.twig', [
            'form'       => $form->createView(),
            'sheet'      => $sheet,
            'sheet_info' => $sheetInfo,
        ]);
    }

    /**
     * @ParamConverter(
     *   "transaction",
     *   class="Proximum\Vimeet\Domain\Model\Transaction",
     *   options={"id" = "transaction_id"}
     * )
     *
     * @param Request     $request
     * @param Sheet       $sheet
     * @param Transaction $transaction
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Sheet $sheet, Transaction $transaction)
    {
        $update = new Update($transaction);
        $form   = $this->createForm(UpdateTransactionType::class, $update);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.transaction.update_handler')->handle($update);
            $this->addFlash('success', 'flash.admin.transaction.update.success');

            return $this->redirectToRoute('admin_sheet_billing', [
                'id'       => $sheet->getEvent()->getId(),
                'sheet_id' => $sheet->getId(),
            ]);
        }

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetInfo($sheet);

        return $this->render('VimeetAppBundle:Admin/Transaction:update.html.twig', [
            'form'       => $form->createView(),
            'sheet'      => $sheet,
            'sheet_info' => $sheetInfo,
        ]);
    }

    /**
     * @ParamConverter(
     *   "transaction",
     *   class="Proximum\Vimeet\Domain\Model\Transaction",
     *   options={"id" = "transaction_id"}
     * )
     *
     * @Method("DELETE")
     *
     * @param Sheet       $sheet
     * @param Transaction $transaction
     *
     * @return RedirectResponse
     */
    public function removeAction(Sheet $sheet, Transaction $transaction)
    {
        $this->get('command.transaction.remove_handler')->handle(new Remove($transaction));
        $this->addFlash('success', 'flash.admin.transaction.remove.success');

        return $this->redirectToRoute('admin_sheet_billing', [
            'id'       => $sheet->getEvent()->getId(),
            'sheet_id' => $sheet->getId(),
        ]);
    }
}
