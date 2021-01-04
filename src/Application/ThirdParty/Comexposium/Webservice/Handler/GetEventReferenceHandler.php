<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\Exception\EventHasNotComexposiumReferenceException;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as ExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class GetEventReferenceHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(ExtraParameterRepositoryInterface $extraParameterRepository)
    {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    /**
     * @throws EventHasNotComexposiumReferenceException
     */
    public function handle(Event $event): string
    {
        $eventReferenceExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            ExtraParameterType::TYPE_COMEXPOSIUM_EVENT_REFERENCE
        );

        if (!$eventReferenceExtraParameter instanceof ExtraParameter) {
            throw new EventHasNotComexposiumReferenceException('Eevent has not Comexposium event reference');
        }

        return $eventReferenceExtraParameter->getValue();
    }
}
