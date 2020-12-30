<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Query;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\Exception\EventHasNotComexposiumReferenceException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\GetEventReferenceHandler;

class HasEventReferenceQueryHandler
{
    /** @var GetEventReferenceHandler */
    private $eventReferenceHandler;

    public function __construct(GetEventReferenceHandler $eventReferenceHandler)
    {
        $this->eventReferenceHandler = $eventReferenceHandler;
    }

    public function handle(HasEventReferenceQuery $eventReferenceQuery): bool
    {
        try {
            $this->eventReferenceHandler->handle($eventReferenceQuery->event);

            return true;
        } catch (EventHasNotComexposiumReferenceException $eventHasNotComexposiumReferenceException) {
            return false;
        }
    }
}
