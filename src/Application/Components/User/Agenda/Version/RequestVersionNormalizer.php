<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\Meeting\Request;

class RequestVersionNormalizer
{
    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function normalize(Request $request): array
    {
        if ($request->hasMeeting()) {
            $meeting = $request->getMeeting();

            $fromParticipants = [];
            $toParticipants   = [];

            foreach ($meeting->getFromParticipants()->toArray() as $fromParticipant) {
                $fromParticipants[$fromParticipant->getId()] = $fromParticipant->getId();
            }

            foreach ($meeting->getToParticipants()->toArray() as $toParticipant) {
                $toParticipants[$toParticipant->getId()] = $toParticipant->getId();
            }

            return [
                'request'          => $request->getId(),
                'fromSheet'        => $request->getFromSheet()->getId(),
                'toSheet'          => $request->getToSheet()->getId(),
                'slot'             => $meeting->getSlot()->getId(),
                'spot'             => $meeting->getSpot()->getId(),
                'fromParticipants' => $fromParticipants,
                'toParticipants'   => $toParticipants,
            ];
        }

        throw new \InvalidArgumentException('The given request does not have a meeting');
    }
}
