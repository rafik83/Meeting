<?php

namespace Proximum\Vimeet\Application\Query\Notification;

use Proximum\Vimeet\Application\Adapter\RouterInterface;

abstract class AbstractNotificationQueryHandler
{
    /** @var RouterInterface */
    protected $router;

    /** @var \DateTimeInterface */
    protected $dateTime;

    public function __construct(RouterInterface $router, \DateTimeInterface $dateTime)
    {
        $this->router = $router;
        $this->dateTime = $dateTime;
    }
}
