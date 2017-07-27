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

interface VideoConferenceInterface
{
    /**
     * @param array $options
     *
     * @return Session
     */
    public function createSession(array $options = []): Session;

    /**
     * @param Session $session
     * @param array   $options
     *
     * @return string
     */
    public function generateAccessToken(Session $session, array $options = []): string;

    /**
     * @return string
     */
    public function getApiKey(): string;
}
