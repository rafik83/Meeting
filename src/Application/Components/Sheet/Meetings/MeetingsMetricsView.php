<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Meetings;

class MeetingsMetricsView
{
    /**
     * @var int
     */
    public $sheetsTotal;

    /**
     * @var int
     */
    public $meetingsTotal;

    /**
     * @var int
     */
    public $requestsTotal;

    /**
     * @var float
     */
    public $transformationTotal;

    /**
     * @var float
     */
    public $averageFilling;

    /**
     * @var float
     */
    public $averageRequestsPropositionsTransformation;

    /**
     * @param int   $sheetsTotal
     * @param int   $meetingsTotal
     * @param int   $requestsTotal
     * @param float $transformationTotal
     * @param float $averageFilling
     * @param float $averageRequestsPropositionsTransformation
     */
    public function __construct(
        $sheetsTotal,
        $meetingsTotal,
        $requestsTotal,
        $transformationTotal,
        $averageFilling,
        $averageRequestsPropositionsTransformation
    ) {
        $this->sheetsTotal                               = $sheetsTotal;
        $this->meetingsTotal                             = $meetingsTotal;
        $this->requestsTotal                             = $requestsTotal;
        $this->transformationTotal                       = $transformationTotal;
        $this->averageFilling                            = $averageFilling;
        $this->averageRequestsPropositionsTransformation = $averageRequestsPropositionsTransformation;
    }
}
