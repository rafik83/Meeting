<?php

namespace Proximum\Vimeet\Application\View\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;

class RequestView
{
    const TYPE_PROPOSITION = 'proposition';
    const TYPE_REQUEST     = 'request';

    /** @var int */
    public $requestId;

    /** @var Request */
    public $request;

    /** @var int */
    public $sheetMetId;

    /** @var string */
    public $sheetMetTitle;

    /** @var Sheet */
    public $sheetMet;

    /** @var string */
    public $state;

    /** @var string */
    public $type;

    /** @var ParticipantView[] */
    public $participantViews;

    /** @var bool */
    public $planned;

    /**
     * @param int               $requestId
     * @param Request           $request
     * @param int               $sheetMetId
     * @param string            $sheetMetTitle
     * @param Sheet             $sheetMet
     * @param string            $state
     * @param string            $type
     * @param ParticipantView[] $participantViews
     * @param bool              $planned
     */
    public function __construct(
        $requestId,
        Request $request,
        $sheetMetId,
        $sheetMetTitle,
        Sheet $sheetMet,
        $state,
        $type,
        array $participantViews,
        $planned = false
    ) {
        $this->requestId        = $requestId;
        $this->request          = $request;
        $this->sheetMetId       = $sheetMetId;
        $this->sheetMetTitle    = $sheetMetTitle;
        $this->sheetMet         = $sheetMet;
        $this->state            = $state;
        $this->type             = $type;
        $this->participantViews = $participantViews;
        $this->planned          = $planned;
    }

    /**
     * @return bool
     */
    public function hasNoPreference()
    {
        return empty($this->participantViews);
    }

    /**
     * @return bool
     */
    public function isProposition()
    {
        return self::TYPE_PROPOSITION === $this->type;
    }
}
