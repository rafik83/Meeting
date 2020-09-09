<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

class CustomPreviewData
{
    const PARTICIPANTS_POSITION = 'custom_preview_data_participant_position';

    const ALL = [
        self::PARTICIPANTS_POSITION,
    ];

    /**
     * @return CustomPreviewDataView[]
     */
    public static function getCustomPreviewDataViews(): array
    {
        $customPreviewDataViews = [];

        foreach (self::ALL as $name) {
            $customPreviewDataViews[$name] = new CustomPreviewDataView($name);
        }

        return $customPreviewDataViews;
    }

    /**
     * @param string $name
     *
     * @return null|CustomPreviewDataView
     */
    public static function getCustomPreviewDataViewByName(string $name): ?CustomPreviewDataView
    {
        $customPreviewDataViews = self::getCustomPreviewDataViews();

        if (array_key_exists($name, $customPreviewDataViews)) {
            return $customPreviewDataViews[$name];
        }

        return null;
    }
}
