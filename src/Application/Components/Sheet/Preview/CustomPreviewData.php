<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

class CustomPreviewData
{
    const PARTICIPANTS_POSITION = 'custom_preview_data_participant_position';

    const ALL = [
        self::PARTICIPANTS_POSITION
    ];

    /**
     * @return array
     */
    public static function getCustomPreviewDataViews(): array
    {
        $customPreviewDataViews = [];

        foreach (self::ALL as $name) {
            $customPreviewDataViews[$name] = new CustomPreviewDataView($name);
        }

        return $customPreviewDataViews;
    }
}
