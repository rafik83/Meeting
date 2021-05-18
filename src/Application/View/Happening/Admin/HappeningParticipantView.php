<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

use Proximum\Vimeet\Application\Serializer\Normalizer\HappeningParticipantNormalizer;

class HappeningParticipantView
{
    private int $happeningId;
    private string $happeningBeginHour;
    private string $happeningEndHour;
    private string $happeningDay;
    private string $happeningTitle;
    private ?int $sheetId;
    private int $participantId;
    // question asked during registration to happening
    private ?string $questionRegister;
    // question(s) asked during webinar
    private ?string $questionsWebinar;
    // reply(ies) for webinar question(s)
    private ?string $replies;
    // votes for webinar question(s)
    private ?string $votes;
    private ?string $questionsDateTimes;
    private string $email;
    private string $firstname;
    private string $lastname;
    private ?string $position;
    private ?string $sheetName;
    private ?string $phone;
    private ?int $evaluation;
    private ?string $connect;

    public function __construct(
        int $happeningId,
        string $happeningBeginHour,
        string $happeningEndHour,
        string $happeningDay,
        string $happeningTitle,
        ?int $sheetId,
        int $participantId,
        ?string $questionRegister,
        ?string $questionsWebinar,
        ?string $replies,
        ?string $votes,
        ?string $questionsDateTimes,
        string $email,
        string $firstname,
        string $lastname,
        ?string $position,
        ?string $sheetName,
        ?string $phone,
        ?int $evaluation,
        ?string $connect
    ) {
        $this->sheetId = $sheetId;
        $this->participantId = $participantId;
        $this->questionRegister = $questionRegister;
        $this->questionsWebinar = $questionsWebinar;
        $this->replies = $replies;
        $this->votes = $votes;
        $this->questionsDateTimes = $questionsDateTimes;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->position = $position;
        $this->sheetName = $sheetName;
        $this->happeningId = $happeningId;
        $this->happeningBeginHour = $happeningBeginHour;
        $this->happeningEndHour = $happeningEndHour;
        $this->happeningDay = $happeningDay;
        $this->happeningTitle = $happeningTitle;
        $this->phone = $phone;
        $this->evaluation = $evaluation;
        $this->connect = $connect;
    }

    public function getHappeningId(): int
    {
        return $this->happeningId;
    }

    public function getHappeningBeginHour(): string
    {
        return $this->happeningBeginHour;
    }

    public function getHappeningEndHour(): string
    {
        return $this->happeningEndHour;
    }

    public function getHappeningDay(): string
    {
        return $this->happeningDay;
    }

    public function getHappeningTitle(): string
    {
        return $this->happeningTitle;
    }

    public function getSheetId(): ?int
    {
        return $this->sheetId;
    }

    public function getParticipantId(): int
    {
        return $this->participantId;
    }

    public function getQuestionRegister(): ?string
    {
        return $this->questionRegister;
    }

    public function getQuestionsWebinar(): ?string
    {
        return $this->questionsWebinar;
    }

    public function getReplies(): ?string
    {
        return $this->replies;
    }

    public function getVotes(): ?string
    {
        return $this->votes;
    }

    public function getQuestionsDateTimes(): ?string
    {
        return $this->questionsDateTimes;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function getSheetName(): ?string
    {
        return $this->sheetName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEvaluation(): ?int
    {
        return $this->evaluation;
    }

    public function getConnect(): ?string
    {
        return $this->connect;
    }

    public function toArray(): array
    {
        return [
            HappeningParticipantNormalizer::COL_HAPPENING_ID => $this->getHappeningId(),
            HappeningParticipantNormalizer::COL_HAPPENING_BEGIN_HOUR => $this->getHappeningBeginHour(),
            HappeningParticipantNormalizer::COL_HAPPENING_END_HOUR => $this->getHappeningEndHour(),
            HappeningParticipantNormalizer::COL_HAPPENING_NAME => $this->getHappeningTitle(),
            HappeningParticipantNormalizer::COL_HAPPENING_DAY => $this->getHappeningDay(),
            HappeningParticipantNormalizer::COL_SHEET_ID => $this->getSheetId(),
            HappeningParticipantNormalizer::COL_SHEET_NAME => $this->getSheetName(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_ID => $this->getParticipantId(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_FIRSTNAME => $this->getFirstname(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_LASTNAME => $this->getLastname(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_EMAIL => $this->getEmail(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_PHONE => $this->getPhone(),
            HappeningParticipantNormalizer::COL_HAPPENING_GRADE => $this->getEvaluation(),
            HappeningParticipantNormalizer::COL_HAPPENING_CONNECT => $this->getConnect(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_POSITION => $this->getPosition(),
            HappeningParticipantNormalizer::COL_QUESTION_REGISTER => $this->getQuestionRegister(),
            HappeningParticipantNormalizer::COL_QUESTIONS_WEBINAR => $this->getQuestionsWebinar(),
            HappeningParticipantNormalizer::COL_REPLIES => $this->getReplies(),
            HappeningParticipantNormalizer::COL_VOTES => $this->getVotes(),
            HappeningParticipantNormalizer::COL_QUESTIONS_DATETIMES => $this->getQuestionsDateTimes(),
        ];
    }
}
