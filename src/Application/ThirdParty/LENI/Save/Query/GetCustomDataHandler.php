<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\HasUserSheetStateChangedQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\HasUserSheetStateChangedQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\SendingRequestDataHandler;

/**
 * Get custom Data to send to Save LENI Api but not to save in the fingerprint
 */
class GetCustomDataHandler
{
    /** @var SendingRequestDataHandler */
    private $sendingRequestDataHandler;

    /** @var HasUserSheetStateChangedQueryHandler */
    private $hasUserSheetStateChangedQueryHandler;

    public function __construct(
        SendingRequestDataHandler $sendingRequestDataHandler,
        HasUserSheetStateChangedQueryHandler $hasUserSheetStateChangedQueryHandler
    ) {
        $this->sendingRequestDataHandler = $sendingRequestDataHandler;
        $this->hasUserSheetStateChangedQueryHandler = $hasUserSheetStateChangedQueryHandler;
    }

    public function handle(GetCustomData $getCustomData): array
    {
        $data = $getCustomData->data;

        // If User is new (No LENI id), set "API" to "EvenementOrigine" field and "Inscrit" to "Inscrit"
        if (!isset($data[LeniConstants::LENI_COL_USER_ID])) {
            $data[LeniConstants::LENI_COL_EVENT_ORIGIN] = LeniConstants::NEW_USER_EVENT_ORIGIN;
            $data[LeniConstants::LENI_COL_ATTENDANCE] = LeniConstants::ATTENDANCE;
        }

        $hasUserSheetStateChangedToValidated = $this->hasUserSheetStateChangedQueryHandler->handle(
            new HasUserSheetStateChangedQuery($getCustomData->event, $getCustomData->user, $data)
        );

        if ($hasUserSheetStateChangedToValidated) {
            // If user has sheet validated, set "Inscrit" field to "Inscrit"
            $data[LeniConstants::LENI_COL_ATTENDANCE] = LeniConstants::ATTENDANCE;
        }

        $sendingRequests = $this->sendingRequestDataHandler->handle(
            new SendingRequestData(
                $getCustomData->event,
                $getCustomData->user,
                $data,
                $hasUserSheetStateChangedToValidated
            )
        );

        if (!empty($sendingRequests)) {
            $data[LeniConstants::LENI_COL_SENDING_REQUEST] = $sendingRequests;
        }

        return $data;
    }
}
