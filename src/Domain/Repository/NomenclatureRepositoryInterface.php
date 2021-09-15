<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;

interface NomenclatureRepositoryInterface
{
    /**
     * @return Nomenclature[]
     */
    public function getAll();

    /**
     * @param Event $event
     *
     * @return Nomenclature[]
     */
    public function findByEvent(Event $event);

    /**
     * @param int $id
     *
     * @return Nomenclature
     */
    public function findById($id);

    /**
     * @param Event $event
     * @param array $ids   array of int
     *
     * @return Nomenclature[]
     */
    public function findByEventAndIds(Event $event, array $ids);

    /**
     * @param Nomenclature $nomenclature
     */
    public function add(Nomenclature $nomenclature);

    /**
     * @param Nomenclature $nomenclature
     */
    public function set(Nomenclature $nomenclature);

    /**
     * @param Nomenclature $nomenclature
     */
    public function remove(Nomenclature $nomenclature);

    /**
     * @return Nomenclature[]
     */
    public function findGlobals();

    /**
     * @param Nomenclature $nomenclature
     * @param Event        $event
     *
     * @return null|Nomenclature
     */
    public function findClone($nomenclature, $event);
}
