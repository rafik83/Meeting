<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use OpenTok\Archive;
use OpenTok\ArchiveList;
use OpenTok\Layout;
use OpenTok\Session;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;

interface VideoConferenceAdapterInterface
{
    /**
     * @param array $options
     *
     * @return Session
     */
    public function createSession(array $options = []): Session;

    /**
     * @param string $sessionId
     *
     * @return Session
     */
    public function getSession(string $sessionId): Session;

    public function archive(string $sessionId, string $name): Archive;

    public function changeArchiveLayout(string $archiveId, Layout $layout): void;

    public function changeStreamClassList(string $sessionId, string $streamId, string $class): void;

    public function stopArchive(string $archiveId): Archive;

    public function getArchive(string $archiveId): Archive;

    public function listArchives(string $sessionId): ArchiveList;

    public function listArchiveUrls(string $sessionId): array;

    /**
     * @param Session            $session
     * @param \DateTimeInterface $endDateTime
     * @param array              $options
     * @param bool               $isPublisher
     *
     * @throws InvalidTokenGeneratorArgumentsException
     *
     * @return string
     */
    public function generateAccessToken(
        Session $session,
        \DateTimeInterface $endDateTime,
        array $options = [],
        bool $isPublisher = true
    ): string;

    /**
     * @return string
     */
    public function getApiKey(): string;
}
