<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use OpenTok\OpenTok;
use OpenTok\Role;
use OpenTok\Session;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceInterface;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class VideoConferenceAdapter implements VideoConferenceAdapterInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var OpenTok
     */
    private $openTok;

    /**
     * VideoConferenceAdapter constructor.
     *
     * @param string $apiKey
     * @param string $apiSecret
     */
    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->openTok = new OpenTok($apiKey, $apiSecret);
    }

    /**
     * {@inheritdoc}
     */
    public function createSession(array $options = []): Session
    {
        return $this->openTok->createSession($options);
    }

    /**
     * {@inheritdoc}
     */
    public function generateAccessToken(Session $session, MeetingSlot $slot, array $options = []): string
    {
        $slotEndDate    = clone $slot->getEnd();
        $sessionEndDate = $slotEndDate->modify('+15 min');

        $defaultOption = [
            'role' => Role::PUBLISHER,
//            'expireTime' => $sessionEndDate->getTimeStamp()
        ];

        return $this->openTok->generateToken($session->getSessionId(),array_merge(
            $defaultOption, $options
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getSession(string $sessionId): Session
    {
        return new Session($this->openTok, $sessionId);
    }
}
