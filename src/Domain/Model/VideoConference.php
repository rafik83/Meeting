<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

class VideoConference
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * @var ArrayCollection
     */
    private $tokens;

    /**
     * VideoConference constructor.
     *
     * @param string  $sessionId
     * @param Meeting $meeting
     */
    public function __construct(string $sessionId, Meeting $meeting)
    {
        $this->sessionId = $sessionId;
        $this->meeting   = $meeting;
        $this->tokens    = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @return Meeting
     */
    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    /**
     * @return array
     */
    public function getTokens(): array
    {
        return $this->tokens->toArray();
    }

    /**
     * @param VideoConferenceToken $token
     */
    public function setToken(VideoConferenceToken $token)
    {
        $this->tokens->add($token);
    }

    /**
     * @param User $user
     *
     * @return null|VideoConferenceToken
     */
    public function getTokenByUser(User $user): ?VideoConferenceToken
    {
        /** @var VideoConferenceToken $token */
        foreach ($this->tokens as $token) {
            if ($token->getUser()->getId() === $user->getId()) {
                return $token;
            }
        }

        return null;
    }
}
