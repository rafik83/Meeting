<?php

namespace Proximum\Vimeet\Application\View\Happening\Admin;

use Proximum\Vimeet\Application\Serializer\Normalizer\HappeningParticipantNormalizer;

class HappeningParticipantView
{
    /**
     * @var int
     */
    private $happeningId;

    /**
     * @var string
     */
    private $happeningBeginHour;

    /**
     * @var string
     */
    private $happeningEndHour;

    /**
     * @var string
     */
    private $happeningDay;

    /**
     * @var string
     */
    private $happeningTitle;

    /**
     * @var int
     */
    private $sheetId;

    /**
     * @var int
     */
    private $participantId;

    /**
     * @var string
     */
    private $question;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $sheetName;

    private ?string $phone;

    private ?int $evaluation;

    private ?string $connect;

    /**
     * HappeningParticipantView constructor.
     *
     * @param int    $happeningId
     * @param string $happeningBeginHour
     * @param string $happeningEndHour
     * @param string $happeningDay
     * @param string $happeningTitle
     * @param int    $sheetId
     * @param int    $participantId
     * @param string $question
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $position
     * @param string $sheetName
     */
    public function __construct(
        $happeningId,
        $happeningBeginHour,
        $happeningEndHour,
        $happeningDay,
        $happeningTitle,
        $sheetId,
        $participantId,
        $question,
        $email,
        $firstname,
        $lastname,
        $position,
        $sheetName,
        ?string $phone,
        ?int $evaluation,
        ?string $connect
    ) {
        $this->sheetId            = $sheetId;
        $this->participantId      = $participantId;
        $this->question           = $question;
        $this->email              = $email;
        $this->firstname          = $firstname;
        $this->lastname           = $lastname;
        $this->position           = $position;
        $this->sheetName          = $sheetName;
        $this->happeningId        = $happeningId;
        $this->happeningBeginHour = $happeningBeginHour;
        $this->happeningEndHour   = $happeningEndHour;
        $this->happeningDay       = $happeningDay;
        $this->happeningTitle     = $happeningTitle;
        $this->phone              = $phone;
        $this->evaluation         = $evaluation;
        $this->connect            = $connect;
    }

    /**
     * @return int
     */
    public function getHappeningId()
    {
        return $this->happeningId;
    }

    /**
     * @return string
     */
    public function getHappeningBeginHour()
    {
        return $this->happeningBeginHour;
    }

    /**
     * @return string
     */
    public function getHappeningEndHour()
    {
        return $this->happeningEndHour;
    }

    /**
     * @return string
     */
    public function getHappeningDay()
    {
        return $this->happeningDay;
    }

    /**
     * @return string
     */
    public function getHappeningTitle()
    {
        return $this->happeningTitle;
    }

    /**
     * @return int
     */
    public function getSheetId()
    {
        return $this->sheetId;
    }

    /**
     * @return int
     */
    public function getParticipantId()
    {
        return $this->participantId;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getSheetName()
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

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            HappeningParticipantNormalizer::COL_HAPPENING_ID          => $this->getHappeningId(),
            HappeningParticipantNormalizer::COL_HAPPENING_BEGIN_HOUR  => $this->getHappeningBeginHour(),
            HappeningParticipantNormalizer::COL_HAPPENING_END_HOUR    => $this->getHappeningEndHour(),
            HappeningParticipantNormalizer::COL_HAPPENING_NAME        => $this->getHappeningTitle(),
            HappeningParticipantNormalizer::COL_HAPPENING_DAY         => $this->getHappeningDay(),
            HappeningParticipantNormalizer::COL_SHEET_ID              => $this->getSheetId(),
            HappeningParticipantNormalizer::COL_SHEET_NAME            => $this->getSheetName(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_ID        => $this->getParticipantId(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_FIRSTNAME => $this->getFirstname(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_LASTNAME  => $this->getLastname(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_EMAIL     => $this->getEmail(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_PHONE     => $this->getPhone(),
            HappeningParticipantNormalizer::COL_HAPPENING_GRADE       => $this->getEvaluation(),
            HappeningParticipantNormalizer::COL_HAPPENING_CONNECT     => $this->getConnect(),
            HappeningParticipantNormalizer::COL_PARTICIPANT_POSITION  => $this->getPosition(),
            HappeningParticipantNormalizer::COL_QUESTION              => $this->getQuestion(),
        ];
    }
}
