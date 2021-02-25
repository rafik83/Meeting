<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Transaction\Create;
use Proximum\Vimeet\Application\Command\Transaction\Remove;
use Proximum\Vimeet\Application\Command\Transaction\Update;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction\CreateTransactionType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction\UpdateTransactionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends AbstractController
{
    private SheetInfoGuesser $sheetInfoGuesser;
    private CommandBusInterface $commandBus;

    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        CommandBusInterface $commandBus
    ) {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->commandBus = $commandBus;
    }

    public function createAction(Request $request, Event $event, Sheet $sheet): Response
    {
        $this->denyAccessIfSheetNotInEvent($event, $sheet);

        $create = new Create($sheet, null, new \DateTime());
        $form   = $this->createForm(CreateTransactionType::class, $create);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.admin.transaction.create.success');

            return $this->redirect($this->generateUrl('admin_sheet_details', [
                    'event' => $event->getId(),
                    'sheet' => $sheet->getId(),
                ]) . '#sheetOrders');
        }

        $sheetInfo = $this->sheetInfoGuesser
            ->guessSheetTitle($sheet, $event->getAvailableLocale($request->getLocale()));

        return $this->render('AdminBundle:Transaction:create.html.twig', [
            'form'       => $form->createView(),
            'event'      => $event,
            'sheet'      => $sheet,
            'sheet_info' => $sheetInfo,
        ]);
    }

    public function updateAction(Request $request, Event $event, Sheet $sheet, Transaction $transaction): Response
    {
        $this->denyAccessIfSheetNotInEvent($event, $sheet);
        $this->denyAccessIfTransactionNotInSheet($sheet, $transaction);

        $locale = $event->getAvailableLocale($request->getLocale());

        $update = new Update($transaction);
        $form   = $this->createForm(UpdateTransactionType::class, $update);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->addFlash('success', 'flash.admin.transaction.update.success');

            return $this->redirect($this->generateUrl('admin_sheet_details', [
                    'event' => $event->getId(),
                    'sheet' => $sheet->getId(),
                ]) . '#sheetOrders');
        }

        $sheetInfo = $this->sheetInfoGuesser
            ->guessSheetTitle($sheet, $locale);

        return $this->render('AdminBundle:Transaction:update.html.twig', [
            'form'       => $form->createView(),
            'event'      => $event,
            'sheet'      => $sheet,
            'sheet_info' => $sheetInfo,
        ]);
    }

    public function removeAction(Event $event, Sheet $sheet, Transaction $transaction): RedirectResponse
    {
        $this->denyAccessIfSheetNotInEvent($event, $sheet);
        $this->denyAccessIfTransactionNotInSheet($sheet, $transaction);

        $this->commandBus->handle(new Remove($transaction));
        $this->addFlash('success', 'flash.admin.transaction.remove.success');

        return $this->redirectToRoute('admin_sheet_details', [
            'event' => $event->getId(),
            'sheet' => $sheet->getId(),
        ]);
    }

    private function denyAccessIfSheetNotInEvent(Event $event, Sheet $sheet): void
    {
        if ($sheet->getEvent() !== $event) {
            throw $this->createAccessDeniedException();
        }
    }

    private function denyAccessIfTransactionNotInSheet(Sheet $sheet, Transaction $transaction): void
    {
        if ($transaction->getSheet() !== $sheet) {
            throw $this->createAccessDeniedException();
        }
    }
}
