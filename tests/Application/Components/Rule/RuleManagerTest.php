<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Rule;

use Proximum\Vimeet\Application\Components\Rule\RuleManager;
use Proximum\Vimeet\Application\Components\Rule\RuleSorter;
use Proximum\Vimeet\Application\Components\Rule\Strategy\SetNullStrategy;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class RuleManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testApplySetNull()
    {
        $data = [
            '563cb103926e5' => [
                '563cb103926e6' => 'Thomas',
                '563cb10a524c5' => '786, place de Valentin 60 573 Leduc',
                '563cb10fcc9fe' => 'http://charles.net/totam-nulla-quam-ipsam-voluptatem-cupiditate-sed-natus-debitis',
                '563cb115bbeb9' => 'Repellendus illo veritatis qui ex. Veritatis voluptate vel possimus omnis aut.',
            ],
            '563cb11d08df0' => [
                '563cb11d08df1' => 'Qui cupiditate eos quod veritatis vel optio provident non.',
            ],
        ];

        $what = [
            'sheet' => [
                '563cb103926e5' => [
                    '563cb103926e6' => true,
                    '563cb10a524c5' => true,
                    '563cb10fcc9fe' => true,
                    '563cb115bbeb9' => false,
                ],
                '563cb11d08df0' => [
                    '563cb11d08df1' => false,
                ],
            ],
        ];

        $expectedData = [
            '563cb103926e5' => [
                '563cb103926e6' => null,
                '563cb10a524c5' => null,
                '563cb10fcc9fe' => null,
                '563cb115bbeb9' => 'Repellendus illo veritatis qui ex. Veritatis voluptate vel possimus omnis aut.',
            ],
            '563cb11d08df0' => [
                '563cb11d08df1' => 'Qui cupiditate eos quod veritatis vel optio provident non.',
            ],
        ];

        $event = new Event();
        $sheet = new Sheet($event, new Type($event), $data, []);
        $rule  = new Rule($event, new Type($event), $sheet->getType(), $what);

        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $manager = new RuleManager($ruleRepository->reveal(), $typeRepository->reveal(), new RuleSorter());

        $manager->apply($rule, $sheet, new SetNullStrategy());

        $this->assertEquals($expectedData, $sheet->getData());
    }

    public function testGetRule()
    {
        $user  = new User('test@test.com', '__SALT__', 'password', 'fr');
        $event = new Event();
        $sheet = new Sheet($event, new Type($event), [], []);

        $types = [
            new Type($event),
        ];

        $rules = [
            new Rule($event, new Type($event), new Category($event), []),
            new Rule($event, new Category($event), new Category($event), []),
            new Rule($event, new Type($event), $sheet->getType(), []),
        ];

        $expectedRule = $rules[2];

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getTypesByUser($event, $user)->shouldBeCalled()->willReturn($types);

        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository->getBySeerTypeAndSeeableType($types[0], $sheet->getType())->shouldBeCalled()->willReturn($rules);

        $manager = new RuleManager($ruleRepository->reveal(), $typeRepository->reveal(), new RuleSorter());

        $rule = $manager->getRule($sheet, $user);

        $this->assertEquals($expectedRule, $rule);
    }
}
