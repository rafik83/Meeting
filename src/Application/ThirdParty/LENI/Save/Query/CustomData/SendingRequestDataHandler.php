<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class SendingRequestDataHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function handle(SendingRequestData $query): array
    {
        $sendingRequest = $this->getSendingRequestParameters($query->event);

        if (empty($sendingRequest)) {
            return [];
        }

        if ($this->isSendingRequestToNewUser($query->data, $sendingRequest)) {
            return $sendingRequest[LeniConstants::SENDING_REQUEST_NEW_USER];
        }

        if ($this->isSendingRequestToValidatedSheet($query->hasUserSheetStateChangedToValidated, $sendingRequest)) {
            return $sendingRequest[LeniConstants::SENDING_REQUEST_SHEET_IS_VALIDATED];
        }

        return [];
    }

    private function getSendingRequestParameters(Event $event): array
    {
        $sendingRequestExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            EventExtraParameterType::TYPE_LENI_SENDING_REQUEST
        );

        if (null === $sendingRequestExtraParameter) {
            return [];
        }

        return json_decode($sendingRequestExtraParameter->getValue(), true);
    }

    private function isSendingRequestToNewUser(array &$data, array &$sendingRequests): bool
    {
        return !isset($data[LeniConstants::LENI_COL_USER_ID])
            && isset($sendingRequests[LeniConstants::SENDING_REQUEST_NEW_USER]);
    }

    private function isSendingRequestToValidatedSheet(
        bool $hasUserSheetStateChangedToValidated,
        array &$sendingRequests
    ): bool {
        return $hasUserSheetStateChangedToValidated
            && isset($sendingRequests[LeniConstants::SENDING_REQUEST_SHEET_IS_VALIDATED]);
    }
}
