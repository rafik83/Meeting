<?php

namespace Proximum\Vimeet\Domain\Repository\Visio;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;

interface VisioSettingsRepositoryInterface
{
    public function create(VisioSettings $visioSettings): void;
    public function update(VisioSettings $visioSettings): void;
    public function getByEvent(Event $event): ?VisioSettings;
}
