<?php

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Spot;

class UnavailabilityBatchResult
{
    /**
     * @var Spot[]
     */
    public $spotsWithMeetingWarning;

    /**
     * @param Spot[] $spotsWithMeetingWarning
     */
    public function __construct(array $spotsWithMeetingWarning = [])
    {
        $this->spotsWithMeetingWarning = $spotsWithMeetingWarning;
    }

    /**
     * @return bool
     */
    public function hasSpotWithMeetingWarning()
    {
        return !empty($this->spotsWithMeetingWarning);
    }

    /**
     * @return string
     */
    public function getSpotReferences()
    {
        return implode(
            ', ',
            array_map(
                function (Spot $spot) {
                    return $spot->getReference();
                },
                $this->spotsWithMeetingWarning
            )
        );
    }
}
