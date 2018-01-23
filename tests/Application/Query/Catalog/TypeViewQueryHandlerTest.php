<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Catalog;

use Proximum\Vimeet\Application\Query\Catalog\TypeViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\TypeViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class TypeViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event  = EventFactory::createEvent();
        $locale = 'fr';

        $typeOne = new Type($event);
        $typeOne->translate($locale, 'type1', 'description');

        $typeTwo = new Type($event);
        $typeTwo->translate($locale, 'type2', 'description');

        $visiblesType = [
            '1' => $typeOne,
            '2' => $typeTwo,
        ];

        $query = new TypeViewQuery($event, $visiblesType, 'fr');

        // Mock
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);

        $typeRepository->getTypesTitleByEventAndLocale(
            $query->event,
            $query->locale,
            $query->visibleTypes
        )->shouldBeCalled()->willReturn([
            '1' => 'type1',
            '2' => 'type2',
        ]);

        $handler   = new TypeViewQueryHandler($typeRepository->reveal());
        $typeViews = $handler->handle($query);
        
        $exceptedTypeViews = [
            '1' => new TypeView(1, 'type1', 0),
            '2' => new TypeView(2, 'type2', 0),
        ];

        $this->assertEquals($exceptedTypeViews, $typeViews);
    }
}
