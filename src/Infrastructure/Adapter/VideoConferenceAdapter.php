<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use InvalidArgumentException;
use OpenTok\OpenTok;
use OpenTok\Role;
use OpenTok\Session;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;
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
     * @var bool
     */
    private $hasSecurity;

    /**
     * VideoConferenceAdapter constructor.
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @param bool $hasSecurity
     */
    public function __construct(string $apiKey, string $apiSecret, bool $hasSecurity)
    {
        $this->apiKey = $apiKey;
        $this->openTok = new OpenTok($apiKey, $apiSecret);
        $this->hasSecurity = $hasSecurity;
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
        $defaultOptions = ['role' => Role::PUBLISHER];

        if ($this->hasSecurity === true) {
            /** @var \DateTime $slotEndDate */
            $slotEndDate = clone $slot->getEnd();
            $sessionEndDate = $slotEndDate->modify('+15 min');

            $defaultOptions = array_merge($defaultOptions, [
                'expireTime' => $sessionEndDate->getTimeStamp(),
            ]);
        }

        try {
            return $this->openTok->generateToken($session->getSessionId(), array_merge(
                $defaultOptions, $options
            ));
        } catch (InvalidArgumentException $argumentException) {
            throw new InvalidTokenGeneratorArgumentsException();
        }
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
