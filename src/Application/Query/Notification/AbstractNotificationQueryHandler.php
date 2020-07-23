<?php

namespace Proximum\Vimeet\Application\Query\Notification;

use Proximum\Vimeet\Application\Adapter\RouterInterface;

abstract class AbstractNotificationQueryHandler
{
    /** @var RouterInterface */
    protected $router;

    /** @var \DateTimeInterface */
    protected $datetime;

    public function __construct(RouterInterface $router, \DateTimeInterface $datetime)
    {
        $this->router = $router;
        $this->datetime = $datetime;
    }
}
