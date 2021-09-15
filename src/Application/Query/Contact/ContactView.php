<?php

namespace Proximum\Vimeet\Application\Query\Contact;

use Proximum\Vimeet\Domain\Participant\GetParticipantInitials;

class ContactView
{
    /** @var int */
    public $seenUserId;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $initials;

    /** @var string */
    public $position;

    /** @var string */
    public $avatar;

    /** @var ContactSheetView[] */
    public $contactSheetViews;

    /** @var int|null */
    public $evaluation;

    /** @var string|null */
    public $comment;

    /**
     * @param int                $seenUserId
     * @param string             $firstName
     * @param string             $lastName
     * @param string             $position
     * @param string             $avatar
     * @param int|null           $evaluation
     * @param string|null        $comment
     * @param ContactSheetView[] $contactSheetViews
     */
    public function __construct(
        int $seenUserId,
        string $firstName,
        string $lastName,
        string $position,
        string $avatar,
        ?int $evaluation,
        ?string $comment,
        array $contactSheetViews
    ) {
        $this->seenUserId = $seenUserId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->initials = (new GetParticipantInitials())($firstName, $lastName);
        $this->position = $position;
        $this->avatar = $avatar;
        $this->contactSheetViews = $contactSheetViews;
        $this->evaluation = $evaluation;
        $this->comment = $comment;
    }
}
