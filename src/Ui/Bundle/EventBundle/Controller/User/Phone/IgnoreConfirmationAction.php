<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone;

use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmation;
use Proximum\Vimeet\Application\Command\User\Phone\IgnoreConfirmationHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\HttpFoundation\JsonResponse;

class IgnoreConfirmationAction
{
    /** @var IgnoreConfirmationHandler */
    private $ignoreConfirmationHandler;

    /**
     * @param IgnoreConfirmationHandler $ignoreConfirmationHandler
     */
    public function __construct(IgnoreConfirmationHandler $ignoreConfirmationHandler)
    {
        $this->ignoreConfirmationHandler = $ignoreConfirmationHandler;
    }

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return JsonResponse
     */
    public function __invoke(Sheet $sheet, Participant $participant)
    {
        $this->ignoreConfirmationHandler->handle(
            new IgnoreConfirmation(
                $sheet->getEvent(),
                $participant
            )
        );

        return new JsonResponse([]);
    }
}
