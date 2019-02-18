<?php

namespace Proximum\Vimeet\Tests\Application\Command\Order\Export;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
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
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

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
    private $fileStorageAdapter;
    
    /** @var ObjectProphecy */
    private $fileRepository;
    
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
        $this->fileStorageAdapter = $this->prophesize(LocalFileStorageAdapter::class);
        $this->fileRepository = $this->prophesize(FileRepositoryInterface::class);
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
    
        $this->event->getId()->willReturn(1);
        $command = new ExportProducts(1, 'test@test.fr', 'fr');
        $view = $this->queryHandler->handle(new ProductsListViewQuery($this->event->reveal(), $command->locale))
            ->willReturn(new ProductsListView($productViews, 'fr'))
        ;
        $data = $this->serializer->serialize($view, 'csv', [
            'charset' => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ]);
    
        $this->fileStorageAdapter->create(
            $data,
            'products_1.csv',
            'super/path'
        )->willReturn('path/to/file/products_1.csv');
    
        $expectedFile = new File('path/to/file/orders_1234.csv', new \DateTime());
        $reflection   = new \ReflectionClass(File::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setAccessible(false);
        $this->fileRepository->add($expectedFile);
        
        $file = $this->prophesize(File::class);
        $fileFactory = $this->prophesize(FileFactory::class);
        
        $fileFactory
            ->createAndPersistFile('path/to/store/export/export_products_list_1_10_00_00_10_01_2019.csv', File::TYPE_EXPORT_PRODUCT_LIST)
            ->willReturn($file->reveal())
        ;
    }
}
