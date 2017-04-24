<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Group\Participant;

class UpdateUsersSheetsResultView
{
    const PARTICIPANT_HAS_MEETING_ON_SHEET = 'group.participant.update.participant_has_meeting_on_sheet';

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
        if (!in_array($type, [self::PARTICIPANT_HAS_MEETING_ON_SHEET])) {
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
}
