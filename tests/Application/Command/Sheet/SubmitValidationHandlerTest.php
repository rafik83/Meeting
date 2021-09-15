<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\SubmitValidation;
use Proximum\Vimeet\Application\Command\Sheet\SubmitValidationHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetSubmittedEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SubmitValidationHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $dateTime = new \DateTime('2017-08-08 08:08:08.000');
        $typeNotAccepted = new Type($event);
        $typeNotAccepted->getValidationCriteria()->setSheetAccepted(false);
        $user          = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet         = new Sheet($event, $typeNotAccepted, [], $user, $dateTime);
        $expectedSheet = new Sheet($event, $typeNotAccepted, [], $user, $dateTime);

        $command = new SubmitValidation($sheet, $user);

        $sheetRepository        = $this->prophesize(SheetRepositoryInterface::class);
        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $expectedSheet->setValidationState(Sheet::STATE_VALIDATION_PENDING);
        $sheetRepository->set($expectedSheet)->shouldBeCalled();

        $delayedEventDispatcher
            ->dispatch(
                Events::SHEET_VALIDATION_PENDING,
                new SheetSubmittedEvent(
                    $expectedSheet,
                    $user
                )
            )->shouldBeCalled();

        $handler = new SubmitValidationHandler($sheetRepository->reveal(), $delayedEventDispatcher->reveal());
        $handler->handle($command);
    }
}
