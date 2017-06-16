<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateDataHandlerTest extends TestCase
{
    public function testHandleWithoutMedia()
    {
        // Input context

        $dateTime       = new \DateTime();
        $event          = EventFactory::createEvent();
        $type           = new Type($event);
        $user           = new User('test@test.com', 'salt', 'password', 'fr');
        $templateData   = new TemplateData('image', ['image' => 'image.jpg', 'product' => 6], 'fr', 'fr');
        $sheet          = new Sheet($event, $type, $templateData->normalize(), $user, $dateTime);
        $templateObject = new TemplateObject('', $type, [], 'fr', 'fr');

        // Expected

        $expectedData = [];

        $expectedSheet = new Sheet($event, $type, $templateData->normalize(), $user, $dateTime);
        $expectedSheet->setData($expectedData);

        $expectedEvent = new SheetUpdatedEvent($expectedSheet);

        // Mock

        $SheetRepository        = $this->prophesize(SheetRepositoryInterface::class);
        $BuyableObjectResolver  = $this->prophesize(BuyableObjectResolver::class);
        $RemoveDataHandler      = $this->prophesize(RemoveDataHandler::class);
        $DelayedEventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        // Preditions / scenario

        $RemoveDataHandler->handle()->shouldNotBeCalled();

        $BuyableObjectResolver->updateCart($sheet, $templateObject)->shouldBeCalled();

        $SheetRepository->set($expectedSheet)->shouldBeCalled();

        $DelayedEventDispatcher->dispatch(Events::SHEET_UPDATED, $expectedEvent)->shouldBeCalled();

        // Command

        $command = new UpdateData($sheet, $templateData, $templateObject);

        // Handler

        $handler = new UpdateDataHandler(
            $SheetRepository->reveal(),
            $BuyableObjectResolver->reveal(),
            $RemoveDataHandler->reveal(),
            $DelayedEventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithOneMedia()
    {
        // Input context

        $dateTime       = new \DateTime();
        $event          = EventFactory::createEvent();
        $type           = new Type($event);
        $user           = new User('test@test.com', 'salt', 'password', 'fr');
        $templateData   = new TemplateData('image', ['image' => 'image.jpg', 'product' => 6], 'fr', 'fr');
        $object1        = new EditableText('69b3cde1', 'editable-text', ['foobar' => 'foobar'], 'fr', 'fr');
        $object2        = new EditableText('69b3cde2', 'editable-text', ['barfoo' => 'barfoo'], 'fr', 'fr');
        $templateData->addChild(0, 'object-1', $object1);
        $templateData->addChild(0, 'object-2', $object2);
        $sheet          = new Sheet($event, $type, $templateData->normalize(), $user, $dateTime);
        $templateObject = new TemplateObject\MediaCollection('', '', [], 'fr', 'fr');
        $templateObject->addMedia(new TemplateObject\Media($templateObject, '', '', ''));

        // Expected

        $expectedData = [
            'object-1' => [],
            'object-2' => [],
        ];

        $expectedSheet = new Sheet($event, $type, $templateData->normalize(), $user, $dateTime);
        $expectedSheet->setData($expectedData);

        $expectedEvent = new SheetUpdatedEvent($expectedSheet);

        // Mock

        $SheetRepository        = $this->prophesize(SheetRepositoryInterface::class);
        $BuyableObjectResolver  = $this->prophesize(BuyableObjectResolver::class);
        $RemoveDataHandler      = $this->prophesize(RemoveDataHandler::class);
        $DelayedEventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        // Preditions / scenario

        $RemoveDataHandler->handle()->shouldNotBeCalled();

        $BuyableObjectResolver->updateCart($sheet, $templateObject)->shouldBeCalled();

        $SheetRepository->set($expectedSheet)->shouldBeCalled();

        $DelayedEventDispatcher->dispatch(Events::SHEET_UPDATED, $expectedEvent)->shouldBeCalled();

        // Command

        $command = new UpdateData($sheet, $templateData, $templateObject);

        // Handler

        $handler = new UpdateDataHandler(
            $SheetRepository->reveal(),
            $BuyableObjectResolver->reveal(),
            $RemoveDataHandler->reveal(),
            $DelayedEventDispatcher->reveal()
        );

        $handler->handle($command);
    }

    public function testHandleWithZeroMedia()
    {
        // Input context

        $dateTime       = new \DateTime();
        $event          = EventFactory::createEvent();
        $type           = new Type($event);
        $user           = new User('test@test.com', 'salt', 'password', 'fr');
        $templateData   = new TemplateData('image', ['image' => 'image.jpg', 'product' => 6], 'fr', 'fr');
        $sheet          = new Sheet($event, $type, $templateData->normalize(), $user, $dateTime);
        $templateObject = new TemplateObject\MediaCollection('', '', [], 'fr', 'fr');

        // Expected

        $expectedRemoveCommand = new RemoveData($templateData, $templateObject, $sheet);

        $expectedData = [];

        $expectedSheet = new Sheet($event, $type, $templateData->normalize(), $user, $dateTime);
        $expectedSheet->setData($expectedData);

        $expectedEvent = new SheetUpdatedEvent($expectedSheet);

        // Mock

        $SheetRepository        = $this->prophesize(SheetRepositoryInterface::class);
        $BuyableObjectResolver  = $this->prophesize(BuyableObjectResolver::class);
        $RemoveDataHandler      = $this->prophesize(RemoveDataHandler::class);
        $DelayedEventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        // Preditions / scenario

        $RemoveDataHandler->handle($expectedRemoveCommand)->shouldBeCalled();

        $BuyableObjectResolver->updateCart($sheet, $templateObject)->shouldBeCalled();

        $SheetRepository->set($expectedSheet)->shouldBeCalled();

        $DelayedEventDispatcher->dispatch(Events::SHEET_UPDATED, $expectedEvent)->shouldBeCalled();

        // Command

        $command = new UpdateData($sheet, $templateData, $templateObject);

        // Handler

        $handler = new UpdateDataHandler(
            $SheetRepository->reveal(),
            $BuyableObjectResolver->reveal(),
            $RemoveDataHandler->reveal(),
            $DelayedEventDispatcher->reveal()
        );

        $handler->handle($command);
    }
}
