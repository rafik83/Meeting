<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Meeting\MessageSubjectInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
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
    public function generateMeetingRequest(Sheet $sheet, Request $request)
    {
        return $this->router->generate('event_meeting_request_show', ['sheet' => $sheet->getId(), 'meetingRequest' => $request->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMeeting(Sheet $sheet, Meeting $meeting)
    {
        return null; // Not implemented yet
    }

    /**
     * {@inheritdoc}
     */
    public function generateSubject(Sheet $sheet, MessageSubjectInterface $subject)
    {
        if ($subject instanceof Request) {
            return $this->generateMeetingRequest($sheet, $subject);
        }

        if ($subject instanceof Meeting) {
            return $this->generateMeeting($sheet, $subject);
        }

        throw new \RuntimeException('Unknown subject type.');
    }

    /**
     * {@inheritdoc}
     */
    public function generateSheet(Sheet $sheet)
    {
        return $this->router->generate('event_sheet', ['sheet' => $sheet->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function generate($path, $parameters)
    {
        return $this->router->generate($path, $parameters);
    }
}
