<?php

namespace Proximum\Vimeet\Application\View\Register;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Template\TemplateData;

class PreFillUserDataView
{
    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var Event|null
     */
    public $event;

    /**
     * @var bool
     */
    private $participationDataPreFilled;

    /**
     * @param TemplateData $templateData
     * @param Event|null   $event
     * @param bool         $participationDataPreFilled
     */
    public function __construct(
        TemplateData $templateData,
        Event $event = null,
        bool $participationDataPreFilled = false
    ) {
        $this->templateData = $templateData;
        $this->participationDataPreFilled = $participationDataPreFilled;
        $this->event = $event;
    }

    /**
     * @return bool
     */
    public function isParticipationDataPreFilled(): bool
    {
        return $this->participationDataPreFilled;
    }
}
