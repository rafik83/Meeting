<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Transaction;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Transaction\Filter as FilterTransaction;
use Proximum\Vimeet\Application\Exception\Event\EventsListEmptyException;
use Proximum\Vimeet\Application\Query\Transaction\TransactionListViewQueryHandler;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction\FilterType as FilterTransactionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends AbstractController
{
    private TransactionListViewQueryHandler $transactionListViewQueryHandler;
    private CommandBusInterface $commandBus;

    public function __construct(
        TransactionListViewQueryHandler $transactionListViewQueryHandler,
        CommandBusInterface $commandBus
    ) {
        $this->transactionListViewQueryHandler = $transactionListViewQueryHandler;
        $this->commandBus = $commandBus;
    }

    public function exportAction(UserInterface $admin, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $filterTransaction = new FilterTransaction($admin);
        $transactionForm = $this->createForm(FilterTransactionType::class, $filterTransaction, ['submit' => true]);

        if ($transactionForm->handleRequest($request)->isSubmitted()) {
            if ($transactionForm->isValid()) {
                try {
                    $transactionListView = $this->commandBus->handle($filterTransaction);
                    $filePath = $this->transactionListViewQueryHandler->handle($transactionListView);

                    return new CsvFileResponse(
                        file_get_contents($this->getParameter('infrastructure.export_transactions_path') . $filePath),
                        sprintf('export_transactions_%s.csv', date('Y_m_d_His'))
                    );
                } catch (EventsListEmptyException $exception) {
                    $this->addFlash('error', 'flash.admin.event.empty_list');
                }
            } else {
                $this->addFlash('error', 'flash.admin.transaction.export.failed');
            }
        }

        return $this->render('AdminBundle:Transaction:export.html.twig', [
           'form' => $transactionForm->createView(),
        ]);
    }
}
