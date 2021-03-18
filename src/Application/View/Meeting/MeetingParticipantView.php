<?php

namespace Proximum\Vimeet\Application\View\Meeting;

class MeetingParticipantView
{
    /** @var string */
    public $firstname;

    /** @var string */
    public $lastname;

    /** @var string */
    public $position;

    /** @var string */
    public $phone;

    /** @var string */
    public $gender;

    /** @var string */
    public $email;

    /** @var string */
    public $evaluation;

    /** @var string */
    public $comment;

    /**
     * @param string      $firstname
     * @param string      $lastname
     * @param string      $position
     * @param string      $phone
     * @param string      $gender
     * @param string      $email
     * @param string|null $evaluation
     * @param string|null $comment
     */
    public function __construct($firstname, $lastname, $position, $phone, $gender, $email, $evaluation, $comment)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->position = $position;
        $this->phone = $phone;
        $this->gender = $gender;
        $this->email = $email;
        $this->evaluation = $evaluation;
        $this->comment = $comment;
    }
}
