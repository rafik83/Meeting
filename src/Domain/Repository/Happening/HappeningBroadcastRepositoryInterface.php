<?php

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;

interface HappeningBroadcastRepositoryInterface
{
    public function add(HappeningBroadcast $happeningBroadcast): void;
    public function update(HappeningBroadcast $happeningBroadcast): void;
    public function deleteForHappening(Happening $happening): void;
    public function getByHappening(Happening $happening): ?HappeningBroadcast;
}
