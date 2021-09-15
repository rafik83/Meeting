<?php

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
    public function getExtraDataByEventAndName(Event $event, string $name): array;

    /**
     * @param Sheet  $sheet
     * @param string $name
     *
     * @return bool
     */
    public function hasExtraDataForSheet(Sheet $sheet, string $name): bool;

    public function getExtraDataForSheet(Sheet $sheet, string $name): ?ExtraData;
}
