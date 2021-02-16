<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Meetings;

class SheetMeetingsListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $meetingsRequestsNumber;

    /**
     * @var int
     */
    public $meetingsPropositionsNumber;

    /**
     * @var int
     */
    public $requestsNumber;

    /**
     * @var int
     */
    public $propositionsNumber;

    /**
     * @var float
     */
    public $requestsTransformation;

    /**
     * @var float
     */
    public $propositionsTransformation;

    /**
     * @var float
     */
    public $requestsPropositionsTransformation;

    /**
     * @var float
     */
    public $transformationTotal;

    /**
     * @param int    $id
     * @param string $title
     * @param string $type
     * @param int    $meetingsRequestsNumber
     * @param int    $meetingsPropositionsNumber
     * @param int    $requestsNumber
     * @param int    $propositionsNumber
     * @param float  $requestsTransformation
     * @param float  $propositionsTransformation
     * @param float  $transformationTotal
     * @param float  $requestsPropositionsTransformation
     */
    public function __construct(
        $id,
        $title,
        $type,
        $meetingsRequestsNumber,
        $meetingsPropositionsNumber,
        $requestsNumber,
        $propositionsNumber,
        $requestsTransformation,
        $propositionsTransformation,
        $transformationTotal,
        $requestsPropositionsTransformation
    ) {
        $this->id                                 = $id;
        $this->title                              = $title;
        $this->type                               = $type;
        $this->meetingsRequestsNumber             = $meetingsRequestsNumber;
        $this->meetingsPropositionsNumber         = $meetingsPropositionsNumber;
        $this->requestsNumber                     = $requestsNumber;
        $this->propositionsNumber                 = $propositionsNumber;
        $this->requestsTransformation             = $requestsTransformation;
        $this->propositionsTransformation         = $propositionsTransformation;
        $this->transformationTotal                = $transformationTotal;
        $this->requestsPropositionsTransformation = $requestsPropositionsTransformation;
    }
}
