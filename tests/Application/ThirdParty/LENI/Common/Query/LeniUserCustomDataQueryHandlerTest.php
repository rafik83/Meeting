<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserCustomDataQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserCustomDataQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class LeniUserCustomDataQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $type = $this->prophesize(Type::class);
        $typeMapping = ['whatever' => 'mapping'];

        $typeConverter = $this->prophesize(TypeConverter::class);
        $typeConverter
            ->convert($type->reveal(), $typeMapping)
            ->shouldBeCalled()
            ->willReturn(['leni_field' => 'value'])
        ;

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping(
                $event->reveal(),
                EventExtraParameterType::TYPE_LENI_TYPES_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn($typeMapping)
        ;

        $leniUserCustomDataQueryHandler = new LeniUserCustomDataQueryHandler(
            $typeConverter->reveal(),
            $mappingGetter->reveal()
        );

        $this->assertEquals(
            ['leni_field' => 'value'],
            $leniUserCustomDataQueryHandler->handle(
                new LeniUserCustomDataQuery($event->reveal(), $user->reveal(), $type->reveal())
            )
        );
    }
}
