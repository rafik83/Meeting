<?php

namespace Proximum\Vimeet\Application\View\Group\Participant;

class UpdateUsersSheetsResultView
{
    const PARTICIPANT_HAS_MEETING_ON_SHEET = 'group.participant.update.participant_has_meeting_on_sheet';
    const PARTICIPANT_HAS_MEETING_REQUEST_ON_SHEET = 'group.participant.update.participant_has_meeting_request_on_sheet';
    const SHEET_MUST_HAVE_AT_LEAST_ONE_PARTICIPANT = 'group.participant.update.sheet_must_have_at_least_one_participant';

    /** @var string */
    public $type;

    /** @var string */
    public $participantFullname;

    /** @var string */
    public $sheetTitle;

    /**
     * @param string $type
     * @param string $participantFullname
     * @param string $sheetTitle
     */
    public function __construct($type, $participantFullname, $sheetTitle)
    {
        if (!in_array($type, [
                self::PARTICIPANT_HAS_MEETING_ON_SHEET,
                self::PARTICIPANT_HAS_MEETING_REQUEST_ON_SHEET,
                self::SHEET_MUST_HAVE_AT_LEAST_ONE_PARTICIPANT,
            ])
        ) {
            throw new  \InvalidArgumentException('Given type is not valid');
        }

        $this->type = $type;
        $this->participantFullname = $participantFullname;
        $this->sheetTitle = $sheetTitle;
    }

    /**
     * @param string $participantFullname
     * @param string $sheetTitle
     *
     * @return UpdateUsersSheetsResultView
     */
    public static function createHasMeetingOnSheet($participantFullname, $sheetTitle)
    {
        return new self(self::PARTICIPANT_HAS_MEETING_ON_SHEET, $participantFullname, $sheetTitle);
    }

    /**
     * @param string $participantFullname
     * @param string $sheetTitle
     *
     * @return UpdateUsersSheetsResultView
     */
    public static function createHasMeetingRequestOnSheet($participantFullname, $sheetTitle)
    {
        return new self(self::PARTICIPANT_HAS_MEETING_REQUEST_ON_SHEET, $participantFullname, $sheetTitle);
    }

    /**
     * @param string $participantFullname
     * @param string $sheetTitle
     *
     * @return UpdateUsersSheetsResultView
     */
    public static function createSheetMustHaveAtLeastOneParticipant($participantFullname, $sheetTitle)
    {
        return new self(self::SHEET_MUST_HAVE_AT_LEAST_ONE_PARTICIPANT, $participantFullname, $sheetTitle);
    }
}
