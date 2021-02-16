<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Planner\TypePriorityViewQuery;
use Proximum\Vimeet\Application\Query\Planner\TypePriorityViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\TypePriorityView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TypePriorityViewQueryHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $ruleRepository;

    /**
     * @var Event
     */
    private $event;

    public function setUp()
    {
        $this->ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $this->event          = EventFactory::createEvent();
    }

    public function testHandle()
    {
        // Data
        $type      = new Type($this->event);
        $type2     = new Type($this->event);
        $type3     = new Type($this->event);
        $typeView  = new TypeView(1, '1title');
        $typeView2 = new TypeView(2, '2title');
        $typeView3 = new TypeView(3, '3title');

        $rule  = new Rule($this->event, $type, $type2, [], 10);
        $rule2 = new Rule($this->event, $type2, $type, [], 2);
        $rule3 = new Rule($this->event, $type, $type3, [], 100);
        $rule4 = new Rule($this->event, $type3, $type2, [], 1);
        $rule5 = new Rule($this->event, $type3, $type, [], 9);

        // Reflection
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($type, 1);
        $property->setValue($type2, 2);
        $property->setValue($type3, 3);
        $property->setAccessible(false);

        // Mock
        $this->ruleRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$rule, $rule2, $rule3, $rule4, $rule5]);

        // Handler
        $handler = new TypePriorityViewQueryHandler($this->ruleRepository->reveal());
        $result  = $handler->handle(new TypePriorityViewQuery($this->event, [$typeView, $typeView2, $typeView3]));

        // Expected
        $expected = [
            new TypePriorityView($typeView3, $typeView2, 1),
            new TypePriorityView($typeView2, $typeView, 2),
            new TypePriorityView($typeView3, $typeView, 9),
            new TypePriorityView($typeView, $typeView2, 10),
            new TypePriorityView($typeView, $typeView3, 100),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHandleCategory()
    {
        // Data
        $type      = new Type($this->event);
        $type2     = new Type($this->event);
        $type3     = new Type($this->event);
        $category  = new Category($this->event);
        $category->setType($type, 1);
        $category->setType($type2, 2);

        $category2 = new Category($this->event);
        $category2->setType($type3, 3);
        $category2->setType($type2, 2);

        $typeView  = new TypeView(1, '1title');
        $typeView2 = new TypeView(2, '2title');
        $typeView3 = new TypeView(3, '3title');

        $rule  = new Rule($this->event, $category2, $type2, [], 10);
        $rule2 = new Rule($this->event, $type2, $category, [], 2);
        $rule3 = new Rule($this->event, $category, $category2, [], 100);
        $rule4 = new Rule($this->event, $category, $category, [], 1);
        $rule5 = new Rule($this->event, $type3, $type, [], 9);

        // Reflection
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($type, 1);
        $property->setValue($type2, 2);
        $property->setValue($type3, 3);
        $property->setAccessible(false);

        // Mock
        $this->ruleRepository->getByEvent($this->event)->shouldBeCalled()->willReturn([$rule, $rule2, $rule3, $rule4, $rule5]);

        // Handler
        $handler = new TypePriorityViewQueryHandler($this->ruleRepository->reveal());
        $result  = $handler->handle(new TypePriorityViewQuery($this->event, [$typeView, $typeView2, $typeView3]));

        // Expected
        $expected = [
            new TypePriorityView($typeView, $typeView, 1), // rule4
            new TypePriorityView($typeView, $typeView2, 1), // rule4
            new TypePriorityView($typeView2, $typeView, 1), // rule4
            new TypePriorityView($typeView2, $typeView2, 1), // rule4
            new TypePriorityView($typeView3, $typeView, 9), // rule5
            new TypePriorityView($typeView3, $typeView2, 10), //rule
            new TypePriorityView($typeView, $typeView3, 100), // rule3
            new TypePriorityView($typeView2, $typeView3, 100), // rule3
        ];

        $this->assertEquals($expected, $result);
    }
}
