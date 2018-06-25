<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQueryHandler;

class GetUserBadgeByEventQueryHandler
{
    /** @var QRCodeIdentifierQueryHandler */
    private $qrCodeIdentifierQueryHandler;

    /** @var QRCodeGeneratorInterface */
    private $qrCodeGenerator;

    public function __construct(
        QRCodeIdentifierQueryHandler $qrCodeIdentifierQueryHandler,
        QRCodeGeneratorInterface $qrCodeGenerator
    ) {
        $this->qrCodeIdentifierQueryHandler = $qrCodeIdentifierQueryHandler;
        $this->qrCodeGenerator = $qrCodeGenerator;
    }

    public function handle(GetUserBadgeByEventQuery $query): UserBadgeByEventView
    {
        $qrCodeIdentifier = $this->qrCodeIdentifierQueryHandler->handle(
            new QRCodeIdentifierQuery($query->event, $query->user)
        );

        return new UserBadgeByEventView(
            'sheet title',
            'first name',
            'last name',
            'user position',
            'participation type',
            $qrCodeIdentifier,
            $this->qrCodeGenerator->generateBase64Image($qrCodeIdentifier)
        );
    }
}
