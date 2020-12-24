<?php

namespace Proximum\Vimeet\Tests\Application\Command\Invoice;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Invoice\BatchGenerateInvoice;
use Proximum\Vimeet\Application\Command\Invoice\BatchGenerateInvoiceHandler;
use Proximum\Vimeet\Application\Command\Invoice\Create;
use Proximum\Vimeet\Application\Command\Invoice\CreateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchGenerateInvoiceHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $date   = new \DateTime();
        $admin  = new Admin('email@email.com', 'test', 'test', 'fr', 'test', 'test', 'ROLE_SUPER_ADMIN', $date);
        $type   = new Type($event);
        $user   = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet  = new Sheet($event, $type, [], $user, $date);
        $prefix = new Prefix('Vimeet', 'Vi');

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $createHandler   = $this->prophesize(CreateHandler::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $jobQueue        = $this->prophesize(JobQueueInterface::class);

        $sheetRepository->getSheetsById([1])->shouldBeCalled()->willReturn([$sheet]);

        $create = new Create($sheet, $prefix);
        $createHandler->handle($create)->shouldBeCalled()->willReturn(
            [
                new Invoice(
                    $event,
                    $sheet,
                    $event->getInvoicePrefix(),
                    'Vi',
                    date('Y'),
                    888,
                    true,
                    'et',
                    20,
                    10,
                    10,
                    10,
                    'EUR',
                    'some-data',
                    $date
                ),
            ]
        );

        $jobQueue->sendEmailing($event, [1], Events::SHEET_INVOICED)->shouldBeCalled();

        $command = new BatchGenerateInvoice($event, [1], $admin);
        $handler = new BatchGenerateInvoiceHandler(
            $sheetRepository->reveal(),
            $createHandler->reveal(),
            $eventDispatcher->reveal(),
            $date,
            $jobQueue->reveal()
        );

        $handler->handle($command);
    }
}
