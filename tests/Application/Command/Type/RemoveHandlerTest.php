<?php

namespace Proximum\Vimeet\Tests\Application\Command\Type;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Type\Remove;
use Proximum\Vimeet\Application\Command\Type\RemoveHandler;
use Proximum\Vimeet\Application\Exception\Type\TypeUsedBySheetException;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveHandlerTest extends TestCase
{
    public function testHandleisThereAtLeastOneByType()
    {
        $this->expectException(TypeUsedBySheetException::class);
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $command = new Remove($type);

        $ruleRepository  = $this->prophesize(RuleRepositoryInterface::class);
        $typeRepository  = $this->prophesize(TypeRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheetRepository->isThereAtLeastOneByType($command->type)->shouldBeCalled()->willReturn(true);

        $handler = new RemoveHandler($typeRepository->reveal(), $sheetRepository->reveal(), $ruleRepository->reveal());
        $handler->handle($command);
    }

    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $command = new Remove($type);

        $ruleRepository  = $this->prophesize(RuleRepositoryInterface::class);
        $typeRepository  = $this->prophesize(TypeRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheetRepository->isThereAtLeastOneByType($command->type)->shouldBeCalled()->willReturn(false);
        $typeRepository->remove($command->type)->shouldBeCalled();

        $handler = new RemoveHandler($typeRepository->reveal(), $sheetRepository->reveal(), $ruleRepository->reveal());
        $handler->handle($command);
    }
}
