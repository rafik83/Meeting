<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use InvalidArgumentException;
use OpenTok\MediaMode;
use OpenTok\OpenTok;
use OpenTok\Role;
use OpenTok\Session;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;

class VideoConferenceAdapter implements VideoConferenceAdapterInterface
{
    const DELAY_AFTER_END_TIME = '+15 minutes';
    const TOKEN_GENERATION_DEFAULT_OPTIONS = ['role' => Role::PUBLISHER];
    const SESSION_DEFAULT_OPTIONS = ['mediaMode' => MediaMode::ROUTED];

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
        return $this->openTok->createSession(array_merge(
            self::SESSION_DEFAULT_OPTIONS,
            $options
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function generateAccessToken(Session $session, \DateTimeInterface $endDateTime, array $options = []): string
    {
        if (true === $this->hasSecurity) {
            $sessionEndDate = new \DateTime($endDateTime->format('Y-m-d H:i:s.u'));

            if (false === $sessionEndDate->modify(self::DELAY_AFTER_END_TIME)) {
                throw new \LogicException('Impossible to modify the date');
            }

            $options['expireTime'] = $sessionEndDate->getTimeStamp();
        }

        try {
            return $this->openTok->generateToken($session->getSessionId(), array_merge(
                self::TOKEN_GENERATION_DEFAULT_OPTIONS, $options
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
