<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PurchasingFunnel;

use Proximum\Vimeet\Domain\Model\PurchasingFunnel;
use Proximum\Vimeet\Domain\Repository\PurchasingFunnelRepositoryInterface;

class CreateHandler
{
    /**
     * @var PurchasingFunnelRepositoryInterface
     */
    private $purchasingFunnelRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateHandler constructor.
     *
     * @param PurchasingFunnelRepositoryInterface $purchasingFunnelRepository
     * @param \DateTimeInterface                  $dateTime
     */
    public function __construct(PurchasingFunnelRepositoryInterface $purchasingFunnelRepository, \DateTimeInterface $dateTime)
    {
        $this->purchasingFunnelRepository = $purchasingFunnelRepository;
        $this->dateTime                   = $dateTime;
    }

    /**
     * @param Create $create
     *
     * @return CreateResult
     */
    public function handle(Create $create)
    {
        $purchasingFunnel = new PurchasingFunnel($create->event, $create->title, $this->dateTime);

        $this->purchasingFunnelRepository->add($purchasingFunnel);

        return new CreateResult($purchasingFunnel);
    }
}
