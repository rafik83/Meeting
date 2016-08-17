<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveImageHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $dateTime);
        $image    = new Image('image', [], 'fr', 'fr');

        $templateData = new TemplateData('image', [], 'fr', 'fr');
        $removeImage  = new RemoveImage($image, $sheet, $templateData);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($sheet)->shouldBeCalled();

        $localFileStorage = $this->prophesize(LocalFileStorageAdapter::class);
        $localFileStorage->remove($image->getImage())->shouldBeCalled();

        $handler = new RemoveImageHandler($sheetRepository->reveal(), $localFileStorage->reveal());
        $handler->handle($removeImage);
    }
}
