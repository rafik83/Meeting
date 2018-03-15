<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\ExtraParameter;

/**
 * All the types used for the $type of Event\ExtraParameter
 *
 * @see ExtraParameter
 */
class Type
{
    public const TYPE_LENI_USER = 'leni_user';
    public const TYPE_LENI_EVENT = 'leni_event';
    public const TYPE_LENI_TYPES_MAPPING = 'leni_participation_types_mapping';
    public const TYPE_LENI_SAVE_ENDPOINT = 'leni_save_endpoint';
    public const TYPE_LENI_GET_ENDPOINT = 'leni_get_endpoint';

    public const TYPE_LENI_MODE = 'leni_mode';
    public const VALUE_LENI_MODE_SAVE = 'save';
    public const VALUE_LENI_MODE_GET = 'get';
    public const VALUE_LENI_MODE_BOTH = 'both';

    // Event Reference in Comexposium
    public const TYPE_COMEXPOSIUM_EVENT_REFERENCE = 'comexposium_event_reference';

    // Vimeet Participation Type Id for imported Comexposium exhibitors
    public const TYPE_COMEXPOSIUM_EXHIBITOR_TYPE_ID = 'comexposium_exhibitor_type_id';

    // Vimeet Participation Type Id for Comexposium visitor
    public const TYPE_COMEXPOSIUM_VISITOR_TYPE_ID = 'comexposium_visitor_type_id';

    public const TYPE_VIANEO_ENDPOINT = 'vianeo_endpoint';
    public const TYPE_VIANEO_CONCERNED_TYPES_ID = 'vianeo_concerned_types_id';

    public const TYPE_COMEXPOSIUM_SSO_ENABLED = 'comexposium_sso_enabled';
    public const TYPE_COMEXPOSIUM_SSO_SALON = 'comexposium_sso_salon';
    public const TYPE_COMEXPOSIUM_SSO_SESSION_SALON = 'comexposium_sso_session_salon';
    public const TYPE_COMEXPOSIUM_SSO_APPLICATION = 'comexposium_sso_application';

    public const TYPES = [
        self::TYPE_LENI_USER,
        self::TYPE_LENI_EVENT,
        self::TYPE_LENI_MODE,
        self::TYPE_LENI_SAVE_ENDPOINT,
        self::TYPE_LENI_GET_ENDPOINT,
        self::TYPE_LENI_TYPES_MAPPING,
        self::TYPE_VIANEO_ENDPOINT,
        self::TYPE_VIANEO_CONCERNED_TYPES_ID,
        self::TYPE_COMEXPOSIUM_EVENT_REFERENCE,
        self::TYPE_COMEXPOSIUM_EXHIBITOR_TYPE_ID,
        self::TYPE_COMEXPOSIUM_VISITOR_TYPE_ID,
        self::TYPE_COMEXPOSIUM_SSO_ENABLED,
        self::TYPE_COMEXPOSIUM_SSO_SALON,
        self::TYPE_COMEXPOSIUM_SSO_SESSION_SALON,
        self::TYPE_COMEXPOSIUM_SSO_APPLICATION,
    ];
}
