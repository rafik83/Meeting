<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

/**
 * Get mapping from Event Extra Parameter
 */
class MappingGetter
{
    private const ALLOWED_EVENT_EXTRA_PARAMETER_TYPE = [
        Type::TYPE_LENI_TYPES_MAPPING,
        Type::TYPE_LENI_DATA_MAPPING,
    ];

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(ExtraParameterRepositoryInterface $extraParameterRepository)
    {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    /**
     * @param Event  $event
     * @param string $eventExtraParameterType
     *
     * @throws \InvalidArgumentException
     *
     * @return array|null
     */
    public function getMapping(Event $event, string $eventExtraParameterType): ?array
    {
        if (!\in_array($eventExtraParameterType, self::ALLOWED_EVENT_EXTRA_PARAMETER_TYPE, true)) {
            throw new \InvalidArgumentException(
                sprintf('$eventExtraParameterType "%s" argument is not valid', $eventExtraParameterType)
            );
        }

        $typesMappingExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            $eventExtraParameterType
        );

        if (!$typesMappingExtraParameter instanceof Event\ExtraParameter) {
            return null;
        }

        return json_decode($typesMappingExtraParameter->getValue(), true);
    }
}
