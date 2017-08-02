<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use OpenTok\Session;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

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
     * @param Session     $session
     * @param MeetingSlot $slot
     * @param array       $options
     *
     * @return string
     */
    public function generateAccessToken(Session $session, MeetingSlot $slot, array $options = []): string;

    /**
     * @return string
     */
    public function getApiKey(): string;
}
