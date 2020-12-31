<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Transaction\TransactionViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class FilterHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime    = new \DateTime();
        $endDate     = new \DateTime('+ 1 day');
        $event       = EventFactory::createEvent();
        $sheet       = SheetFactory::create($event);
        $admin       = new Admin('test@test.com', '__salt__', null, 'fr', 'Jeff', 'Atwood', Admin::ROLE_ORGANIZER, $dateTime);
        $type        = new Type($event);
        $transaction = new Transaction($sheet, 100, $dateTime, 'paypal', '42', 'paid', 'EUR');
        $command     = new Filter($admin);

        $command->beginDate = $dateTime;
        $command->endDate   = $endDate;

        $admin->setEvents([$event]);
        $admin->setTypeEvents([$type]);

        $eventRepository             = $this->prophesize(EventRepositoryInterface::class);
        $transactionRepository       = $this->prophesize(TransactionRepositoryInterface::class);
        $transactionViewQueryHandler = $this->prophesize(TransactionViewQueryHandler::class);

        $eventRepository
            ->getEventsByAdmin($admin)
            ->shouldBeCalled()
            ->willReturn([$event]);

        $transactionRepository
            ->getFilteredByEvents([$event], $command->beginDate, $command->endDate)
            ->shouldBeCalled()
            ->willReturn([$transaction]);

        $filterHandler = new FilterHandler(
            $transactionRepository->reveal(),
            $eventRepository->reveal(),
            $transactionViewQueryHandler->reveal()
        );

        $filterHandler->handle($command);
    }
}
