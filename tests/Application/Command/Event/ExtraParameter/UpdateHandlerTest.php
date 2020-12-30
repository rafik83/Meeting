<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\ExtraParameter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Update;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\UpdateHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $dateTime = new \DateTime('2017-09-10 10:10:10.000');
        $updatedAt = new \DateTime();
        $extraParameter = new ExtraParameter($event->reveal(), Type::TYPE_LENI_USER, 'name', 'value', $dateTime);

        $expected = new ExtraParameter($event->reveal(), Type::TYPE_LENI_USER, 'name', 'value', $dateTime);
        $expected->update('other-name', 'other-value', $updatedAt);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository->set($expected)->shouldBeCalled();

        $update = new Update($extraParameter);
        $update->name = 'other-name';
        $update->value = 'other-value';

        $handler = new UpdateHandler($extraParameterRepository->reveal(), $updatedAt);
        $handler->handle($update);
    }
}
