<?php

namespace Proximum\Vimeet\Tests\Application\Components\Rule;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Rule\RuleSorter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RuleSorterTest extends TestCase
{
    public static function provideRules()
    {
        $event = EventFactory::createEvent();

        return [
            [
                [
                    new Rule($event, new Category($event), new Category($event), [], 1),
                    new Rule($event, new Type($event), new Type($event), [], 1),
                ],
                [
                    new Rule($event, new Type($event), new Type($event), [], 1),
                    new Rule($event, new Category($event), new Category($event), [], 1),
                ],
            ],
            [
                [
                    new Rule($event, new Category($event), new Category($event), [], 1),
                    new Rule($event, new Type($event), new Type($event), [], 1),
                    new Rule($event, new Type($event), new Category($event), [], 1),
                ],
                [
                    new Rule($event, new Type($event), new Type($event), [], 1),
                    new Rule($event, new Type($event), new Category($event), [], 1),
                    new Rule($event, new Category($event), new Category($event), [], 1),
                ],
            ],
            [
                [
                    new Rule($event, new Category($event), new Category($event), [], 1),
                    new Rule($event, new Type($event), new Type($event), [], 1),
                    new Rule($event, new Category($event), new Type($event), [], 1),
                    new Rule($event, new Type($event), new Category($event), [], 1),
                ],
                [
                    new Rule($event, new Type($event), new Type($event), [], 1),
                    new Rule($event, new Category($event), new Type($event), [], 1),
                    new Rule($event, new Type($event), new Category($event), [], 1),
                    new Rule($event, new Category($event), new Category($event), [], 1),
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideRules
     *
     * @param array $rules
     * @param array $expected
     */
    public function testSort(array $rules, array $expected)
    {
        $sorter = new RuleSorter();
        $sorter->sort($rules);

        $this->assertEquals($expected, $rules);
    }
}
