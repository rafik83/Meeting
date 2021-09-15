<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Order\Export\ExportOrders;
use Proximum\Vimeet\Application\Command\Order\Export\ExportOrdersHandler;
use Proximum\Vimeet\Application\Query\Order\Export\OrdersExportViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\OrdersExportViewQueryHandler;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Order\Export\OrdersExportView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\ExportOrdersMail;

class ExportOrdersHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(1234);
        $view  = $this->prophesize(OrdersExportView::class);
        $data                  ="z;y;x;\na;b;c;\n1;2;3;";
        $dataWithoutFirstLine  ="a;b;c;\n1;2;3;";

        $eventRepository             = $this->prophesize(EventRepositoryInterface::class);
        $serializer                  = $this->prophesize(SerializerAdapterInterface::class);
        $queryHandler                = $this->prophesize(OrdersExportViewQueryHandler::class);
        $fileStorageAdapter          = $this->prophesize(LocalFileStorageAdapter::class);
        $fileRepository              = $this->prophesize(FileRepositoryInterface::class);
        $mailer                      = $this->prophesize(MailerInterface::class);
        $dateTime                    = new \DateTime();
        $mailSender                  = 'email@sender.fr';
        $exportLocationDirectoryPath = 'super/path';

        $eventRepository->getById(1234)->shouldBeCalled()->willReturn($event->reveal());
        $queryHandler->handle(new OrdersExportViewQuery($event->reveal(), 'fr'))->shouldBeCalled()->willReturn($view->reveal());
        $serializer->serialize($view->reveal(), 'csv', [
            'csv_delimiter' => ';',
            'charset' => Charset::WINDOWS_1252,
        ])->shouldBeCalled()->willReturn($data);

        $fileStorageAdapter->create(
            $dataWithoutFirstLine,
            'orders_1234.csv',
            $exportLocationDirectoryPath
        )->shouldBeCalled()->willReturn('path/to/file/orders_1234.csv');

        $expectedFile = new File('path/to/file/orders_1234.csv', $dateTime);
        $reflection   = new \ReflectionClass(File::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setAccessible(false);
        $fileRepository->add($expectedFile)->shouldBeCalled();

        $mailer->send(new ExportOrdersMail(
            $event->reveal(),
            $mailSender,
            'email@admin.fr',
            'fr',
            $expectedFile->getHash(),
            $expectedFile->getId()
        ))->shouldBeCalled();

        $handler = new ExportOrdersHandler(
            $eventRepository->reveal(),
            $serializer->reveal(),
            $queryHandler->reveal(),
            $fileStorageAdapter->reveal(),
            $fileRepository->reveal(),
            $mailer->reveal(),
            $mailSender,
            $exportLocationDirectoryPath,
            $dateTime
        );

        $handler->handle(new ExportOrders(1234, 'email@admin.fr', 'fr'));
    }
}
