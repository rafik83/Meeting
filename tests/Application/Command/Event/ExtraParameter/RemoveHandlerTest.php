<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\ExtraParameter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Remove;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\RemoveHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class RemoveHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);

        $extraParameter = new ExtraParameter($event->reveal(), Type::TYPE_LENI_USER, 'name', 'value', $dateTime);

        // Mock
        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository->remove($extraParameter)->shouldBeCalled();

        // Command
        $remove = new Remove($extraParameter);

        //Handler
        $handler = new RemoveHandler($extraParameterRepository->reveal());
        $handler->handle($remove);
    }
}
