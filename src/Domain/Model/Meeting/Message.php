<?php

namespace Proximum\Vimeet\Domain\Model\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class Message
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $content;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var Sheet
     */
    private $from;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * Message constructor.
     *
     * @param MessageSubjectInterface $subject
     * @param Sheet                   $from
     * @param string                  $content
     * @param \DateTimeInterface      $createdAt
     */
    public function __construct(MessageSubjectInterface $subject, Sheet $from, $content, \DateTimeInterface $createdAt)
    {
        if ($subject instanceof Meeting) {
            $this->meeting = $subject;
        } elseif ($subject instanceof Request) {
            $this->request = $subject;
        }

        $this->from      = $from;
        $this->content   = $content;
        $this->createdAt = $createdAt;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MessageSubjectInterface
     */
    public function getSubject()
    {
        return $this->meeting ? $this->meeting : $this->request;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get createdAt
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get from
     *
     * @return Sheet
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Sheet
     */
    public function getTo()
    {
        return $this->from === $this->getSubject()->getFromSheet() ?
            $this->getSubject()->getToSheet() :
            $this->getSubject()->getFromSheet();
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get meeting
     *
     * @return Meeting
     */
    public function getMeeting()
    {
        return $this->meeting;
    }
}
