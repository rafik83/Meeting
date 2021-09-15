<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\RemoveDataHandler;
use Proximum\Vimeet\Application\Command\Sheet\RemoveImage;
use Proximum\Vimeet\Application\Command\Sheet\RemoveImageHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveImageHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $dateTime);
        $image    = new Image('69b3cde1', 'image', [], 'fr', 'fr');

        $templateData = new TemplateData('image', [], 'fr', 'fr');
        $removeImage  = new RemoveImage($image, $sheet, $templateData);

        $removeDataHandler = $this->prophesize(RemoveDataHandler::class);

        $localFileStorage = $this->prophesize(LocalFileStorageAdapter::class);
        $localFileStorage->remove($image->getImage())->shouldBeCalled();

        $handler = new RemoveImageHandler(
            $localFileStorage->reveal(),
            $removeDataHandler->reveal()
        );

        $handler->handle($removeImage);
    }
}
