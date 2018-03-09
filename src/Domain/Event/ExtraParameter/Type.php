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
    const TYPE_LENI_USER = 'leni_user';
    const TYPE_LENI_EVENT = 'leni_event';
    const TYPE_LENI_MODE = 'leni_mode';

    const TYPE_LENI_MODE_SAVE_VALUE = 'save';
    const TYPE_LENI_MODE_GET_VALUE = 'get';
    const TYPE_LENI_MODE_BOTH_VALUE = 'both';

    // Event Reference in Comexposium
    const TYPE_COMEXPOSIUM_EVENT_REFERENCE = 'comexposium_event_reference';

    // Vimeet Participation Type Id for imported Comexposium exhibitors
    const TYPE_COMEXPOSIUM_EXHIBITOR_TYPE_ID = 'comexposium_exhibitor_type_id';

    // Vimeet Participation Type Id for Comexposium visitor
    const TYPE_COMEXPOSIUM_VISITOR_TYPE_ID = 'comexposium_visitor_type_id';

    const TYPE_VIANEO_ENDPOINT = 'vianeo_endpoint';
    const TYPE_VIANEO_CONCERNED_TYPES_ID = 'vianeo_concerned_types_id';

    public const TYPE_COMEXPOSIUM_SSO_ENABLED = 'comexposium_sso_enabled';
    public const TYPE_COMEXPOSIUM_SSO_SALON = 'comexposium_sso_salon';
    public const TYPE_COMEXPOSIUM_SSO_SESSION_SALON = 'comexposium_sso_session_salon';
    public const TYPE_COMEXPOSIUM_SSO_APPLICATION = 'comexposium_sso_application';

    public const TYPES = [
        self::TYPE_LENI_USER,
        self::TYPE_LENI_EVENT,
        self::TYPE_LENI_MODE,
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
