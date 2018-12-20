<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AvailabilityAction
{
    public function __invoke(Request $request, Event $event, User $user): JsonResponse
    {
        $arrivalDate = $request->get('arrivalDate', null);
        $departureDate = $request->get('departureDate', null);

        return new JsonResponse([
            'accommodation' => [
                2 => 'Novotel',
                1 => 'CR7',
            ],
            'roommate' => [
                1 => [
                    'label' => 'Jean Dupont',
                    'disabled' => true,
                ],
                2 => [
                    'label' => 'Truc Muche',
                    'disabled' => false,
                ],
                3 => [
                    'label' => 'Amélie Poulain',
                    'disabled' => false,
                ],
            ]
        ]);
    }
}
