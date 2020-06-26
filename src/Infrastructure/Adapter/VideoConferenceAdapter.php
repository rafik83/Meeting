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
use OpenTok\Archive;
use OpenTok\Layout;
use OpenTok\MediaMode;
use OpenTok\OpenTok;
use OpenTok\OutputMode;
use OpenTok\Role;
use OpenTok\Session;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Exception\VideoConference\InvalidTokenGeneratorArgumentsException;

class VideoConferenceAdapter implements VideoConferenceAdapterInterface
{
    const DELAY_AFTER_END_TIME = '+15 minutes';
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

    public function archive(string $sessionId, string $name): Archive
    {
        return $this->openTok->startArchive(
            $sessionId,
            [
                'name' => $name,
                'hasVideo' => true,
                'hasAudio' => true,
                'outputMode' => OutputMode::COMPOSED,
            ]
        );
    }

    public function changeArchiveLayout(string $archiveId, Layout $layout): void
    {
        $this->openTok->setArchiveLayout($archiveId, $layout);
    }

    public function changeStreamClassList(string $sessionId, string $streamId, string $class): void
    {
        $this->openTok->updateStream(
            $sessionId,
            $streamId,
            [
                'layoutClassList' => [$class]
            ]
        );
    }

    public function stopArchive(string $archiveId): Archive
    {
        return $this->openTok->stopArchive($archiveId);
    }

    /**
     * {@inheritdoc}
     */
    public function generateAccessToken(
        Session $session,
        \DateTimeInterface $endDateTime,
        array $options = [],
        bool $isPublisher = true
    ): string {
        if (true === $this->hasSecurity) {
            $sessionEndDate = new \DateTime($endDateTime->format('Y-m-d H:i:s.u'));

            if (false === $sessionEndDate->modify(self::DELAY_AFTER_END_TIME)) {
                throw new \LogicException('Impossible to modify the date');
            }

            $options['expireTime'] = $sessionEndDate->getTimeStamp();
        }

        $options['role'] = $isPublisher ? Role::PUBLISHER : Role::SUBSCRIBER;

        try {
            return $this->openTok->generateToken($session->getSessionId(), $options);
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
