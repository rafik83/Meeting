<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order\Export;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Product\Export\ExportProducts;
use Proximum\Vimeet\Application\Command\Product\Export\ExportProductsHandler;
use Proximum\Vimeet\Application\Query\Product\ProductsListViewQuery;
use Proximum\Vimeet\Application\Query\Product\ProductsListViewQueryHandler;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Product\ProductsListView;
use Proximum\Vimeet\Application\View\Product\ProductsView;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\ExportProductsMail;

class ExportProductsHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $exportHandler;
    
    /** @var ObjectProphecy */
    private $queryHandler;
    
    /** @var ObjectProphecy */
    private $event;
    
    /** @var ObjectProphecy */
    private $eventRepository;
    
    /** @var ObjectProphecy */
    private $view;
    
    /** @var ObjectProphecy */
    private $serializer;
    
    /** @var ObjectProphecy */
    private $file;
    
    /** @var ObjectProphecy */
    private $fileStorage;
    
    /** @var ObjectProphecy */
    private $fileRepository;
    
    /** @var ObjectProphecy */
    private $fileFactory;
    
    /** @var ObjectProphecy */
    private $mailer;
    
    public function setUp()
    {
        $this->exportHandler = $this->prophesize(ExportProductsHandler::class);
        $this->queryHandler = $this->prophesize(ProductsListViewQueryHandler::class);
        $this->event = $this->prophesize(Event::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->view = $this->prophesize(ProductsListView::class);
        $this->serializer = $this->prophesize(SerializerAdapterInterface::class);
        $this->file = $this->prophesize(File::class);
        $this->fileStorage = $this->prophesize(FileStorageInterface::class);
        $this->fileRepository = $this->prophesize(FileRepositoryInterface::class);
        $this->fileFactory = $this->prophesize(FileFactory::class);
        $this->mailer = $this->prophesize(MailerInterface::class);
    }
    
    public function testHandle(): void
    {
        $productViews[0] = new ProductsView(
            'test1',
            1,
            1,
            1,
            2,
            0,
            2
        );
        $productViews[1] = new ProductsView(
            'test2',
            1,
            1,
            1,
            2,
            0,
            2
        );
        $productViews[2] = new ProductsView(
            'test3',
            1,
            1,
            1,
            2,
            0,
            2
        );
        $data ="z;y;x;\na;b;c;\n1;2;3;";
        $dateTime = new \DateTime();
        
        $command = new ExportProducts(1, 'test@test.fr', 'fr');
        $handler = new ExportProductsHandler(
            $this->eventRepository->reveal(),
            $this->serializer->reveal(),
            $this->queryHandler->reveal(),
            $this->fileStorage->reveal(),
            $this->fileRepository->reveal(),
            $this->fileFactory->reveal(),
            $this->mailer->reveal(),
            'test@test.fr',
            'super/path',
            $dateTime
        );
    
        $this->eventRepository->getById(1)->shouldBeCalled()->willReturn( $this->event->reveal());
    
        $list = new ProductsListView($productViews, 'fr');
        $this->queryHandler->handle(new ProductsListViewQuery($this->event->reveal(), $command->locale))
            ->shouldBeCalled()
            ->willReturn($list)
        ;
    
        $this->serializer->serialize($list, 'csv', [
            'charset' => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ])->shouldBeCalled()->willReturn($data);
        
        $fileName = sprintf('export_product_list_%s.csv', $dateTime->format('H_i_s_d_m_Y'));
        $this->fileStorage->create(
            $data,
            $fileName,
            'super/path'
        )->shouldBeCalled()->willReturn('path/to/file/'.$fileName);
    
        $this->fileFactory
            ->createAndPersistFile('path/to/file/'.$fileName, File::TYPE_EXPORT_PRODUCT_LIST)
            ->shouldBeCalled()
            ->willReturn($this->file->reveal())
        ;
        $this->file->getHash()->shouldBeCalled()->willReturn('azerty1234.csv');
        $this->file->getId()->shouldBeCalled()->willReturn(1);
        
        $this->mailer->send(new ExportProductsMail(
            $this->event->reveal(),
            'test@test.fr',
            'test@test.fr',
            'fr',
            'azerty1234.csv',
            1
        ))->shouldBeCalled();
        
        $handler->handle($command);
    }
}
