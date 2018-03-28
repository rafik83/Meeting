<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class LeniUserCustomDataQueryHandler
{
    /** @var TypeConverter */
    private $typeConverter;

    /** @var MappingGetter */
    private $mappingGetter;

    public function __construct(
        TypeConverter $typeConverter,
        MappingGetter $mappingGetter
    ) {
        $this->typeConverter = $typeConverter;
        $this->mappingGetter = $mappingGetter;
    }

    public function handle(LeniUserCustomDataQuery $leniUserCustomDataQuery): array
    {
        return $this->handleType($leniUserCustomDataQuery->event, $leniUserCustomDataQuery->type);
    }

    private function handleType(Event $event, Type $type): array
    {
        $typesMapping = $this->mappingGetter->getMapping(
            $event,
            EventExtraParameterType::TYPE_LENI_TYPES_MAPPING
        );

        if (null === $typesMapping) {
            return [];
        }

        return $this->typeConverter->convert($type, $typesMapping);
    }
}
