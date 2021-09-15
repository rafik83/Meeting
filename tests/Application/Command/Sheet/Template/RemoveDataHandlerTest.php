<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\RemoveData;
use Proximum\Vimeet\Application\Command\Sheet\RemoveDataHandler;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveDataHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime     = new \DateTime();
        $event        = EventFactory::createEvent();
        $type         = new Type($event);
        $user         = new User('test@test.com', 'salt', 'password', 'fr');
        $templateData = new TemplateData('image', ['image' => 'image.jpg', 'product' => 6], 'fr', 'fr');
        $sheet        = new Sheet($event, $type, $templateData->normalize(), $user, $dateTime);
        $image        = new Image('key', 'image', ['image' => 'image.jpg', 'product' => 6], 'fr', 'fr');

        $removeData = new RemoveData($templateData, $image, $sheet);

        $expectedTemplateData = new TemplateData('image', [], 'fr', 'fr');
        $expectedSheet        = new Sheet($event, $type, $expectedTemplateData->getData(), $user, $dateTime);

        $sheetRepository       = $this->prophesize(SheetRepositoryInterface::class);
        $buyableObjectResolver = $this->prophesize(BuyableObjectResolver::class);

        $buyableObjectResolver->removePayableProduct($sheet, $image)->shouldBeCalled();
        $sheetRepository->set($expectedSheet)->shouldBeCalled();

        $handler = new RemoveDataHandler(
            $sheetRepository->reveal(),
            $buyableObjectResolver->reveal()
        );

        $handler->handle($removeData);
    }
}
