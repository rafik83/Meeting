<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\ExtraData;

interface ExtraDataRepositoryInterface
{
    /**
     * @param ExtraData $extraData
     */
    public function add(ExtraData $extraData);

    /**
     * @param ExtraData $extraData
     */
    public function set(ExtraData $extraData);

    /**
     * @param Event    $event
     * @param string   $name
     * @param string[] $values
     *
     * @return ExtraData[]
     */
    public function getExtraDataValuesForEvent(Event $event, string $name, array $values): array;

    /**
     * @return ExtraData[]
     */
    public function getExtraDataByNameAndEvent(Event $event, string $name): array;

    /**
     * @param Sheet  $sheet
     * @param string $name
     *
     * @return bool
     */
    public function hasExtraDataForSheet(Sheet $sheet, string $name): bool;
}
