<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\PostBatch;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchValidationValidate;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchValidationValidateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidationValidateEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchValidationValidateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $sheet1 = SheetFactory::create($event);
        $sheet2 = SheetFactory::create($event);
        $sheet3 = SheetFactory::create($event);
        $admin  = new Admin('john@doe.com', 'salt', 'password', 'fr', 'john', 'doh', 'ROLE_ADMIN', new \DateTime());

        // Mock
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $datetime        = new \DateTime();

        $eventDispatcher->dispatch(
            Events::SHEET_VALIDATION_VALIDATE,
            Argument::type(SheetValidationValidateEvent::class)
        )->shouldBeCalledTimes(3);

        $query   = new PostBatchValidationValidate([$sheet1, $sheet2, $sheet3], $admin);
        $handler = new PostBatchValidationValidateHandler(
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($query);
    }
}
