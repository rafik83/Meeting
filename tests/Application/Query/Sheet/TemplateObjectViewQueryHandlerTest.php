<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\TemplateObjectViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\TemplateObjectViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\TemplateObjectView;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class TemplateObjectViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $templateDataFactory    = $this->prophesize(TemplateDataFactory::class);
        $templateProductGuesser = $this->prophesize(TemplateProductGuesser::class);

        $key             = 'azerzesq';
        $sheet           = SheetFactory::create();
        $templateObject  = new TemplateObject($key, 'image', ['label' => 'testLabel'], null, null);
        $event           = EventFactory::createEvent();
        $product1        = ProductFactory::create($event);
        $product2        = ProductFactory::create($event);
        $buyableProducts = [$product1, $product2];

        $templateData = new TemplateData('image', [], 'fr', '');
        $templateData->addChild(1, $key, $templateObject);

        $sheet->getType()->setPackage(new Package($event, 'package title', new \DateTime()));

        $templateDataFactory->createFromSheet($sheet, null)->shouldBeCalled()->willReturn($templateData);
        $templateProductGuesser
            ->getProducts($templateObject, $sheet->getType()->getPackage())
            ->willReturn($buyableProducts);

        $expectedResult = new TemplateObjectView(
            $templateObject,
            'testLabel'
        );

        $result = (new TemplateObjectViewQueryHandler(
            $templateDataFactory->reveal(),
            $templateProductGuesser->reveal()
        ))->handle(new TemplateObjectViewQuery($sheet, null, $key));

        $this->assertEquals($expectedResult, $result);
    }
}
