<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

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
