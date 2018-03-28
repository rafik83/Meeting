<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class SendingRequestDataHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(ExtraParameterRepositoryInterface $extraParameterRepository)
    {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function handle(SendingRequestData $query): array
    {
        // Currently, only sending_request_new_user parameter is handle, therefore, it can be early return if the
        // Leni id of the user is already present
        if (isset($query->data[LeniConstants::LENI_COL_USER_ID])) {
            return [];
        }

        $sendingRequestParam = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_LENI_SENDING_REQUEST);

        if ($sendingRequestParam === null) {
            return [];
        }

        $sendingRequest = json_decode($sendingRequestParam->getValue(), true);

        if (isset($sendingRequest['sending_request_new_user'])) {
            return $sendingRequest['sending_request_new_user'];
        }

        return [];
    }
}
