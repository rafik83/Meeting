<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

class HappeningParticipantQuestions
{
    private string $questionRegister = '';
    private array $questionsWebinar = [];
    private array $replies = [];
    private array $votes = [];
    private array $dateTimes = [];

    public function setQuestionRegister(string $value)
    {
        $this->questionRegister = $value;
    }

    public function addQuestionWebinar(string $content, ?string $reply, int $votes, string $createdAt)
    {
        $this->questionsWebinar[] = $content;
        $this->replies[] = $reply;
        $this->votes[] = $votes;
        $this->dateTimes[] = $createdAt;
    }

    public function getQuestionRegister(): string
    {
        return $this->questionRegister;
    }

    public function getQuestionsWebinar(): string
    {
        return implode("\n", $this->questionsWebinar);
    }

    public function getReplies(): string
    {
        if (count($this->replies) > 1) {
            $replies = array_map(fn ($r) => $r?:'[empty]', $this->replies);
        } else {
            $replies = $this->replies;
        }

        return implode("\n", $replies);
    }

    public function getVotes(): string
    {
        return implode("\n", $this->votes);
    }

    public function getDateTimes(): string
    {
        return implode("\n", $this->dateTimes);
    }
}
