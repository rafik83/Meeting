<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Notification;

use Proximum\Vimeet\Application\Adapter\RouterInterface;

abstract class AbstractNotificationQueryHandler
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var \DateTimeInterface
     */
    protected $datetime;

    /**
     * AbstractNotificationQueryHandler constructor.
     *
     * @param RouterInterface    $router
     * @param \DateTimeInterface $datetime
     */
    public function __construct(RouterInterface $router, \DateTimeInterface $datetime)
    {
        $this->router   = $router;
        $this->datetime = $datetime;
    }
}
