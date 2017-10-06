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

class VideoConferenceAdapter implements VideoConferenceAdapterInterface
{
    const DELAY_AFTER_END_TIME = '+15 min';

    /** @var OpenTok */
    private $openTok;

    /** @var string */
    private $tokboxApiKey;

    /** @var bool */
    private $hasSecurity;

    /**
     * @param OpenTok $openTok
     * @param string  $tokboxApiKey
     * @param bool    $hasSecurity
     */
    public function __construct(OpenTok $openTok, string $tokboxApiKey, bool $hasSecurity)
    {
        $this->openTok = $openTok;
        $this->tokboxApiKey = $tokboxApiKey;
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
    public function generateAccessToken(Session $session, \DateTimeInterface $endDateTime, array $options = []): string
    {
        $defaultOptions = ['role' => Role::PUBLISHER];

        if ($this->hasSecurity === true) {
            $sessionEndDate = clone $endDateTime;

            if (false === $sessionEndDate->modify(self::DELAY_AFTER_END_TIME)) {
                throw new \LogicException('Imposible do modify a date');
            }

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
        return $this->tokboxApiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getSession(string $sessionId): Session
    {
        return new Session($this->openTok, $sessionId);
    }
}
