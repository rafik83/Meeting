<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Sheet\SheetExtraDataType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class RemoveAlreadyImportedReferences
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(ExtraDataRepositoryInterface $extraDataRepository)
    {
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * @param Event    $event
     * @param string[] $registrationReferences
     *
     * @return array
     */
    public function handle(Event $event, array $registrationReferences): array
    {
        $registrationReferencesExtraData = $this->extraDataRepository->getExtraDataValuesForEvent(
            $event,
            SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE,
            $registrationReferences
        );

        foreach ($registrationReferencesExtraData as $registrationReferenceExtraData) {
            $key = array_search($registrationReferenceExtraData->getValue(), $registrationReferences, true);

            if (false !== $key) {
                unset($registrationReferences[$key]);
            }
        }

        if (empty($registrationReferences)) {
            return [];
        }

        return \array_values($registrationReferences);
    }
}
