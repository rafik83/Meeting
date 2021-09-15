<?php

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet;

final class VianeoTemplateTag
{
    const VIANEO_REGISTRATION    = 'vianeo_registration';
    const VIANEO_CATEGORY        = 'vianeo_category';
    const VIANEO_PROJECT_SUMMARY = 'vianeo_project_summary';

    /**
     * @return array
     */
    public static function getAllTags(): array
    {
        return [
            self::VIANEO_REGISTRATION,
            self::VIANEO_CATEGORY,
            self::VIANEO_PROJECT_SUMMARY,
        ];
    }
}
