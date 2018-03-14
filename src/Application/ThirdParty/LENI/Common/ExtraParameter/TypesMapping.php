<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\ExtraParameter;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

/**
 * Get Participation Types mapping from Event Extra Parameter
 */
class TypesMapping
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(ExtraParameterRepositoryInterface $extraParameterRepository)
    {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function getTypesMapping(Event $event): ?array
    {
        $typesMappingExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_LENI_TYPES_MAPPING
        );

        if (!$typesMappingExtraParameter instanceof Event\ExtraParameter) {
            return null;
        }

        return json_decode($typesMappingExtraParameter->getValue(), true);
    }
}
