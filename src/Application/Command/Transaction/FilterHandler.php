<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Application\Exception\Event\EventsListEmptyException;
use Proximum\Vimeet\Application\Query\Transaction\TransactionListViewQuery;
use Proximum\Vimeet\Application\Query\Transaction\TransactionViewQuery;
use Proximum\Vimeet\Application\Query\Transaction\TransactionViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class FilterHandler
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var TransactionViewQueryHandler
     */
    private $transactionViewQueryHandler;

    /**
     * FindHandler constructor.
     *
     * @param TransactionRepositoryInterface $transactionRepository
     * @param EventRepositoryInterface       $eventRepository
     * @param TransactionViewQueryHandler    $transactionViewQueryHandler
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        EventRepositoryInterface $eventRepository,
        TransactionViewQueryHandler $transactionViewQueryHandler
    ) {
        $this->transactionRepository       = $transactionRepository;
        $this->eventRepository             = $eventRepository;
        $this->transactionViewQueryHandler = $transactionViewQueryHandler;
    }

    /**
     * @param Filter $command
     *
     * @throws EventsListEmptyException
     *
     * @return TransactionListViewQuery
     */
    public function handle(Filter $command)
    {
        $events = $this->eventRepository->getEventsByAdmin($command->admin);

        if (empty($events)) {
            throw new EventsListEmptyException();
        }

        // Set time to encompass the entire day
        $beginDate = $command->beginDate->setTime(0, 0);
        $endDate   = $command->endDate->setTime(23, 59, 59);

        $transactions = $this->transactionRepository->getFilteredByEvents(
            $events,
            $beginDate,
            $endDate
        );

        $this->transactionViewQueryHandler->preloadBillingInfo(array_map(function (Transaction $transaction) {
            return $transaction->getSheet();
        }, $transactions));

        $transactionViews = [];

        foreach ($transactions as $transaction) {
            $transactionViews[] = $this->transactionViewQueryHandler->handle(
                new TransactionViewQuery(
                    $transaction,
                    $transaction->getSheet(),
                    $transaction->getSheet()->getEvent(),
                    $transaction->getPayment()
                )
            );
        }

        return new TransactionListViewQuery($transactionViews, $command->admin->getLocale());
    }
}
