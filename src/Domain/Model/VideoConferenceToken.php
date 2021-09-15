<?php

namespace Proximum\Vimeet\Domain\Model;

class VideoConferenceToken
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var VideoConference
     */
    private $videoConference;

    /**
     * @var string
     */
    private $token;

    /**
     * @var null|string
     */
    private $streamId;

    /**
     * @var User
     */
    private $user;

    /**
     * VideoConferenceToken constructor.
     *
     * @param VideoConference $videoConference
     * @param User            $user
     * @param string          $token
     */
    public function __construct(VideoConference $videoConference, User $user, string $token)
    {
        $this->videoConference = $videoConference;
        $this->token           = $token;
        $this->user            = $user;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return VideoConference
     */
    public function getVideoConference(): VideoConference
    {
        return $this->videoConference;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return null|string
     */
    public function getStreamId(): ?string
    {
        return $this->streamId;
    }

    /**
     * @param null|string $streamId
     */
    public function setStreamId(?string $streamId)
    {
        $this->streamId = $streamId;
    }
}
