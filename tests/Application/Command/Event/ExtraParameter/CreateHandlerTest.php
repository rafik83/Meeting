<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\ExtraParameter;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Create;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\CreateHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Exception\Event\ExtraParameter\ExtraParameterAlreadyExistForThisTypeAndEventException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var \DateTime */
    private $dateTime;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->dateTime = new \DateTime();
    }

    public function testHandleAlreadyExist()
    {
        $otherExtraParameter = $this->prophesize(Event\ExtraParameter::class);
        $this->expectException(ExtraParameterAlreadyExistForThisTypeAndEventException::class);

        $command = new Create($this->event->reveal());
        $command->type = Type::TYPE_LENI_USER;
        $command->value = 'value';
        $command->name = 'name';

        // Mock
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_USER)
            ->shouldBeCalled()
            ->willReturn($otherExtraParameter->reveal())
        ;

        $handler = new CreateHandler($this->extraParameterRepository->reveal(), $this->dateTime);
        $handler->handle($command);
    }

    public function testHandle()
    {
        $command = new Create($this->event->reveal());
        $command->type = Type::TYPE_LENI_USER;
        $command->value = 'value';
        $command->name = 'name';

        // Mock
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_LENI_USER)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $expected = new Event\ExtraParameter(
            $this->event->reveal(),
            Type::TYPE_LENI_USER,
            'name',
            'value',
            $this->dateTime
        );
        $this->extraParameterRepository->add($expected)->shouldBeCalled();

        $handler = new CreateHandler($this->extraParameterRepository->reveal(), $this->dateTime);
        $handler->handle($command);
    }
}
