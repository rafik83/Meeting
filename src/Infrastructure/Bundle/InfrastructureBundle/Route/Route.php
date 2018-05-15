<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route;

final class Route
{
    const EVENT = 'event';
    const DEFAULT_EVENT = 'default_event';
    const EXTERNAL_CATALOG = 'event_catalog_external_index';

    /**
     * Event login
     */
    public const LOGIN = 'event_login';
    public const EVENT_LOGIN_CHECK = 'event_login_check';

    /**
     * User Event login
     */
    public const USER_EVENT_AUTHENTICATION_TOKEN_LOGIN = 'vimeet_event_authentication_token_login';
}
