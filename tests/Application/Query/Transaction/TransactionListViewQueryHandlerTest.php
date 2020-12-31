<?php

namespace Proximum\Vimeet\Tests\Application\Query\Transaction;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Transaction\TransactionListViewQuery;
use Proximum\Vimeet\Application\Query\Transaction\TransactionListViewQueryHandler;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Transaction\TransactionView;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;

class TransactionListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $serializer     = $this->prophesize(SerializerAdapter::class);
        $fileStorage    = $this->prophesize(LocalFileStorageAdapter::class);
        $view           = $this->prophesize(TransactionView::class);
        $dateTime       = new \DateTime();
        $exportDir      = 'export/dir';
        $data           = "a;b;c;\n1;2;3";

        $transactionListViewQuery = new TransactionListViewQuery([$view->reveal()], 'fr');

        $serializer
            ->serialize($transactionListViewQuery, 'csv', [
                'csv_delimiter' => ';',
                'charset'       => Charset::WINDOWS_1252,
            ])
            ->shouldBeCalled()
            ->willReturn($data);

        $fileStorage
            ->create($data, sprintf('transaction_%s.csv', $dateTime->getTimestamp()), $exportDir)
            ->shouldBeCalled()
            ->willReturn('path/to/export/transaction.csv');

        $handler = new TransactionListViewQueryHandler(
            $serializer->reveal(),
            $fileStorage->reveal(),
            $exportDir,
            $dateTime
        );

        $handler->handle($transactionListViewQuery);
    }
}
