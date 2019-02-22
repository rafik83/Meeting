<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Type;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class HasUnavailabilityManagementDisabled
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter)
    {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        $type = $sheet->getType();

        return Type::TYPE_MANAGEMENT_UNAVAILABLE !== $type->getAvailabilityType()
            && false === $this->authorizationCheckerAdapter->isGranted('ROLE_PREVIOUS_ADMIN')
        ;
    }
}
