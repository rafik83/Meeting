<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SSORegistrationTypeResolver
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->extraParameterRepository = $extraParameterRepository;
        $this->typeRepository = $typeRepository;
    }

    public function handle(Event $event): ?Type
    {
        $typeExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            EventExtraParameterType::TYPE_COMEXPOSIUM_VISITOR_TYPE_ID
        );

        if (!$typeExtraParameter instanceof Event\ExtraParameter) {
            return null;
        }

        $typeId = (int) $typeExtraParameter->getValue();

        if (0 === $typeId) {
            return null;
        }

        return $this->typeRepository->getById($typeId);
    }
}
