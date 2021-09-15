<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route;

/**
 * @deprecated use Application/Components/Navigation/Route.php instead
 */
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
