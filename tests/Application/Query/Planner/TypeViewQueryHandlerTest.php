<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planner\TypeViewQuery;
use Proximum\Vimeet\Application\Query\Planner\TypeViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TypeViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $type   = new Type($event);
        $type->translate('fr', 'title', 'description');
        $type2  = new Type($event);
        $type2->translate('fr', 'secondTitle', 'secondDescription');

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getTypesByEvent($event)->shouldBeCalled()->willReturn([$type, $type2]);

        // Reflection
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($type, 1);
        $property->setValue($type2, 2);
        $property->setAccessible(false);

        // Handler
        $handler = new TypeViewQueryHandler($typeRepository->reveal());
        $result = $handler->handle(new TypeViewQuery($event, 'fr'));

        $expected = [
            new TypeView(1, 'title'),
            new TypeView(2, 'secondTitle'),
        ];

        $this->assertEquals($expected, $result);
    }
}
