<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\User\Event;

use Proximum\Vimeet\Application\Components\User\Event\AuthenticationTokenImportParser;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;

class AuthenticationTokenImportPreviewQueryHandler
{
    /** @var AuthenticationTokenImportParser */
    private $authenticationTokenImportParser;

    public function __construct(AuthenticationTokenImportParser $authenticationTokenImportParser)
    {
        $this->authenticationTokenImportParser = $authenticationTokenImportParser;
    }

    /**
     * @param AuthenticationTokenImportPreviewQuery $authenticationTokenImportPreviewQuery
     *
     * @return AuthenticationTokenImport[]
     */
    public function handle(AuthenticationTokenImportPreviewQuery $authenticationTokenImportPreviewQuery): array
    {
        return $this->authenticationTokenImportParser->parse(
            $authenticationTokenImportPreviewQuery->event,
            $authenticationTokenImportPreviewQuery->importedFile
        );
    }
}
