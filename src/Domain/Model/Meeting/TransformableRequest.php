<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Meeting;

class TransformableRequest
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public static function isTransformable(Request $request)
    {
        $from = $request->getFromSheet();
        $to   = $request->getToSheet();

        if ($request->isOneOfSheetsNotAttend()) {
            return false;
        }

        // oneToOne meeting with no preference
        if ((1 === $from->getParticipants()->count() && $request->hasNoPreference($from))
            && (1 === $to->getParticipants()->count() && $request->hasNoPreference($to))
        ) {
            return true;
        }

        // oneToMany with no preference
        if ($from->getParticipants()->count() > 1
            && $request->hasNoPreference($from)
        ) {
            return false;
        }

        // oneToMany with no preference other side
        if ($to->getParticipants()->count() > 1
            && $request->hasNoPreference($to)
        ) {
            return false;
        }

        // Other request with no preference
        if ($request->hasNoPreference($from) && $request->hasNoPreference($to)) {
            return false;
        }

        return true;
    }
}
