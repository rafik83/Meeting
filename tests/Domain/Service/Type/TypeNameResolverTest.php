<?php

namespace Proximum\Vimeet\Tests\Domain\Service\Type;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class TypeNameResolverTest extends TestCase
{
    private $typeRepository;

    public function setUp()
    {
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);
    }

    public function testResolveException()
    {
        $this->expectException(\InvalidArgumentException::class);

        // Context
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        // Mock
        $this->typeRepository
            ->getFirstPositionTypeByEventAndUser($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $typeNameResolver = new TypeNameResolver($this->typeRepository->reveal());
        $typeNameResolver->resolve($user->reveal(), $event->reveal(), 'fr');
    }

    public function testResolve()
    {
        // Context
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        // Mock
        $this->typeRepository
            ->getFirstPositionTypeByEventAndUser($event->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($type)
        ;

        // Expected
        $type->getTitle('fr')->shouldBeCalled()->willReturn('type title in fr');

        $typeNameResolver = new TypeNameResolver($this->typeRepository->reveal());
        $result = $typeNameResolver->resolve($user->reveal(), $event->reveal(), 'fr');

        $expected = 'type title in fr';

        $this->assertEquals($expected, $result);
    }

    public function testResolveWithPreloadedSheetsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $typeNameResolver = new TypeNameResolver($this->typeRepository->reveal());
        $sheets = [];
        $typeNameResolver->resolveWithPreloadedSheets($sheets, 'fr');
    }

    public function testResolveWithPreloadedSheetsWithTypePosition()
    {
        // Context
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $sheet1->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type2->reveal());
        $sheet3->getType()->willReturn($type1->reveal());

        // Expected
        $type2->getTitle('fr')->shouldBeCalled()->willReturn('type title in fr');
        $type1->getTitle('fr')->shouldNotBeCalled();
        $type1->getPosition()->shouldBeCalled()->willReturn(4);
        $type2->getPosition()->shouldBeCalled()->willReturn(1);

        $typeNameResolver = new TypeNameResolver($this->typeRepository->reveal());
        $sheets = [$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()];

        $result = $typeNameResolver
            ->resolveWithPreloadedSheets($sheets, 'fr')
        ;

        $expected = 'type title in fr';

        $this->assertEquals($expected, $result);
    }

    public function testResolveWithPreloadedSheetsWithoutTypePosition()
    {
        // Context
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $sheet1->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type2->reveal());
        $sheet3->getType()->willReturn($type1->reveal());

        // Expected
        $type1->getTitle('fr')->shouldBeCalled()->willReturn('Type 1');
        $type2->getTitle('fr')->shouldNotBeCalled()->willReturn('Type 2');
        $type1->getPosition()->shouldBeCalled()->willReturn(null);
        $type1->getId()->shouldBeCalled()->willReturn(200);
        $type2->getId()->shouldBeCalled()->willReturn(300);

        $typeNameResolver = new TypeNameResolver($this->typeRepository->reveal());
        $sheets = [$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()];

        $result = $typeNameResolver
            ->resolveWithPreloadedSheets($sheets, 'fr')
        ;

        $this->assertEquals('Type 1', $result);
    }
}
