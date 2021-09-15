<?php

namespace Proximum\Vimeet\Domain\Repository\StaticFormulation;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;

interface StaticFormulationRepositoryInterface
{
    public function add(StaticFormulation $staticFormulation): void;
    public function set(StaticFormulation $staticFormulation): void;

    /**
     * @param Event $event
     *
     * @return StaticFormulation[]
     */
    public function findByEvent(Event $event): array;

    /**
     * @param Event  $event
     * @param string $key
     *
     * @return StaticFormulation[]
     */
    public function findByEventAndKey(Event $event, string $key): array;

    public function remove(StaticFormulation $staticFormulation): void;

    /**
     * @param Type   $type
     * @param string $locale
     *
     * @return StaticFormulation[]
     */
    public function findByTypeAndLocale(Type $type, string $locale): array;
}
