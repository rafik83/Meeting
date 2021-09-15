<?php

namespace Proximum\Vimeet\Domain\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\Meeting\Request;

class RequestVersionNormalizer
{
    /**
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function normalize(Request $request): array
    {
        if (!$request->hasMeeting()) {
            throw new \InvalidArgumentException('The given request does not have a meeting');
        }

        $meeting = $request->getMeeting();

        return [
            'request'   => $request->getId(),
            'fromSheet' => $request->getFromSheet()->getId(),
            'toSheet'   => $request->getToSheet()->getId(),
            'slot'      => $meeting->getSlot()->getId(),
            'spot'      => $meeting->getSpot()->getId(),
        ];
    }
}
