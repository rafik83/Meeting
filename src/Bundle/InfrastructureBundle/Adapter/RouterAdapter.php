<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Symfony\Component\Routing\RouterInterface as SymfonyRouterInterface;

class RouterAdapter implements RouterInterface
{
    /**
     * @var SymfonyRouterInterface
     */
    private $router;

    /**
     * RouterAdapter constructor.
     *
     * @param SymfonyRouterInterface $router
     */
    public function __construct(SymfonyRouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function generateMeetingRequest(Request $request)
    {
        return $this->router->generate('event_meeting_request_show', ['meetingRequest' => $request->getId()]);
    }
}
