<?php

namespace Proximum\Vimeet\Application\View\Unavailability;

class CreateUnavailabilitiesResultsView
{
    /** @var CreateUnavailabilitiesResultView[] */
    public $results;

    public function __construct(array $results)
    {
        $this->results = $results;
    }
}
