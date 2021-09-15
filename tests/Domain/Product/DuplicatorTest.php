<?php

namespace Proximum\Vimeet\Tests\Domain\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\Duplicator;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicateProducts()
    {
        $event        = EventFactory::createEvent();
        $currentEvent = EventFactory::createEvent();
        $dateTime     = new \DateTime();

        $product = new Product(
            $event,
            Product::TYPE_OPTION,
            'name',
            'image',
            10,
            20,
            10,
            20,
            30,
            true,
            $dateTime,
            true,
            $dateTime
        );
        $product2 = new Product(
            $event,
            Product::TYPE_PARTICIPANT,
            'participant',
            'image3',
            10,
            20,
            null,
            20,
            30,
            true,
            $dateTime,
            true,
            $dateTime
        );

        $reflection = new \ReflectionClass(Product::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($product, 7);
        $property->setValue($product2, 23);
        $property->setAccessible(false);

        $expectedProduct = new Product(
            $currentEvent,
            Product::TYPE_OPTION,
            'name',
            'image2',
            10,
            20,
            10,
            20,
            30,
            true,
            $dateTime,
            true,
            $dateTime
        );
        $expectedProduct2 = new Product(
            $currentEvent,
            Product::TYPE_PARTICIPANT,
            'participant',
            'image4',
            10,
            20,
            null,
            20,
            30,
            true,
            $dateTime,
            true,
            $dateTime
        );
        $expectedProduct->translate('fr', '', '', '', '', '');
        $expectedProduct->translate('en', '', '', '', '', '');
        $expectedProduct2->translate('fr', '', '', '', '', '');
        $expectedProduct2->translate('en', '', '', '', '', '');

        $fileStorage       = $this->prophesize(FileStorageInterface::class);
        $fileStorage->copyAndRename('image')->shouldBeCalled()->willReturn('image2');
        $fileStorage->copyAndRename('image3')->shouldBeCalled()->willReturn('image4');

        $duplicator = new Duplicator($fileStorage->reveal());
        $products = [$product, $product2];
        $result = $duplicator->duplicateProducts($currentEvent, $products);

        $expected = [
            7 => $expectedProduct,
            23 => $expectedProduct2,
        ];

        $this->assertEquals($expected, $result);
    }
}
