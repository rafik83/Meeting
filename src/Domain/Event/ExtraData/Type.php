<?php

namespace Proximum\Vimeet\Domain\Event\ExtraData;

/**
 * Constants for Event\ExtraData name
 */
final class Type
{
    public const COMEXPOSIUM_SSO_JWT_TOKEN = 'comexposium_sso_jwt_token';
    public const ADMIN_SHEET_BATCH_IDS = 'admin_sheet_batch_ids';
    public const ADMIN_SHEET_EXPORT_IDS = 'admin_sheet_export_ids';
    public const ADMIN_USER_BATCH_IDS = 'admin_user_batch_ids';
    public const ADMIN_PARTICIPANT_IDS = 'admin_participant_ids';
    public const LENI_GET_LAST_UPDATED_AT = 'leni_get_last_updated_at';
}
