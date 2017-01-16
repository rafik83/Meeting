<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Import;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantImportLogger;
use Proximum\Vimeet\Domain\View\Normalizer\ParticipantDenormalizerView;

class ImportResultViewQueryHandler
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * ImportResultViewQueryHandler constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return ParticipantDenormalizerView
     */
    public function handle()
    {
        $loggerData = $this->session->get(ParticipantImportLogger::SESSION_FLASH);

        return new ParticipantDenormalizerView(
            $loggerData[ParticipantImportLogger::DATABASE_PARTICIPATIONS],
            $loggerData[ParticipantImportLogger::FILE_PARTICIPATIONS],
            $loggerData[ParticipantImportLogger::CREATED_SHEETS],
            $loggerData[ParticipantImportLogger::CREATED_USERS],
            $loggerData[ParticipantImportLogger::IMPORT_ERRORS]
        );
    }
}
