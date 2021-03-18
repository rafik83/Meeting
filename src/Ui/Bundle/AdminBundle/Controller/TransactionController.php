<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Transaction\Create;
use Proximum\Vimeet\Application\Command\Transaction\Remove;
use Proximum\Vimeet\Application\Command\Transaction\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction\CreateTransactionType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction\UpdateTransactionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfSheetNotInEvent($event, $sheet);

        $create = new Create($sheet, null, new \DateTime());
        $form   = $this->createForm(CreateTransactionType::class, $create);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.transaction.create.success');

            return $this->redirect($this->generateUrl('admin_sheet_details', [
                    'event' => $event->getId(),
                    'sheet' => $sheet->getId(),
                ]) . '#sheetOrders');
        }

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetTitle($sheet, $event->getAvailableLocale($request->getLocale()));

        return $this->render('AdminBundle:Transaction:create.html.twig', [
            'form'       => $form->createView(),
            'event'      => $event,
            'sheet'      => $sheet,
            'sheet_info' => $sheetInfo,
        ]);
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param Sheet       $sheet
     * @param Transaction $transaction
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, Sheet $sheet, Transaction $transaction)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfSheetNotInEvent($event, $sheet);
        $this->denyAccessIfTransactionNotInSheet($sheet, $transaction);

        $locale = $event->getAvailableLocale($request->getLocale());

        $update = new Update($transaction);
        $form   = $this->createForm(UpdateTransactionType::class, $update);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.transaction.update.success');

            return $this->redirect($this->generateUrl('admin_sheet_details', [
                    'event' => $event->getId(),
                    'sheet' => $sheet->getId(),
                ]) . '#sheetOrders');
        }

        $sheetInfo = $this
            ->get('vimeet_infrastructure.application.components.sheet.sheet_info_guesser')
            ->guessSheetName($sheet, $locale);

        return $this->render('AdminBundle:Transaction:update.html.twig', [
            'form'       => $form->createView(),
            'event'      => $event,
            'sheet'      => $sheet,
            'sheet_info' => $sheetInfo,
        ]);
    }

    /**
     * @param Event       $event
     * @param Sheet       $sheet
     * @param Transaction $transaction
     *
     * @return RedirectResponse
     */
    public function removeAction(Event $event, Sheet $sheet, Transaction $transaction)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessIfSheetNotInEvent($event, $sheet);
        $this->denyAccessIfTransactionNotInSheet($sheet, $transaction);

        $this->get('tactician.commandbus')->handle(new Remove($transaction));
        $this->addFlash('success', 'flash.admin.transaction.remove.success');

        return $this->redirectToRoute('admin_sheet_details', [
            'event' => $event->getId(),
            'sheet' => $sheet->getId(),
        ]);
    }

    /**
     * @param Event $event
     * @param Sheet $sheet
     */
    private function denyAccessIfSheetNotInEvent(Event $event, Sheet $sheet)
    {
        if ($sheet->getEvent() !== $event) {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @param Sheet       $sheet
     * @param Transaction $transaction
     */
    private function denyAccessIfTransactionNotInSheet(Sheet $sheet, Transaction $transaction)
    {
        if ($transaction->getSheet() !== $sheet) {
            throw $this->createAccessDeniedException();
        }
    }
}
