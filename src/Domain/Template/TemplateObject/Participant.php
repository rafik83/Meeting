<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class Participant extends TemplateObject
{
    /**
     * @return int|float
     */
    public function getNumberOfParticipantShown()
    {
        return isset($this->config['numberOfParticipantShown']) ? (int) $this->config['numberOfParticipantShown'] : INF;
    }

    /**
     * {@inheritdoc}
     */
    public function isExportable()
    {
        return false;
    }
}
