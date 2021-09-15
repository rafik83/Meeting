<?php

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Time\TimeOverlap;

class HappeningsNotOverlapped
{
    /**
     * @param Happening[] $happenings
     *
     * @return Happening[]
     */
    public function getHappeningsNotOverlapped(array $happenings): array
    {
        $happeningsOverlapped = $this->getHappeningsOverlapped($happenings);
        $happeningsNotOverlapped = [];

        foreach ($happenings as $happening) {
            if (isset($happeningsOverlapped[$happening->getId()])) {
                continue;
            }

            $happeningsNotOverlapped[] = $happening;
        }

        return $happeningsNotOverlapped;
    }

    /**
     * @param Happening[] $happenings
     *
     * @return Happening[]
     */
    private function getHappeningsOverlapped(array $happenings): array
    {
        $happeningsOverlapped = [];

        foreach ($happenings as $happening) {
            foreach ($happenings as $otherHappening) {
                if ($otherHappening === $happening) {
                    continue;
                }

                if (TimeOverlap::overlap($happening, $otherHappening)) {
                    $happeningsOverlapped[$happening->getId()] = $happening;
                }
            }
        }

        return $happeningsOverlapped;
    }
}
