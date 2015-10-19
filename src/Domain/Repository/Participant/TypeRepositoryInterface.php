<?php

namespace Proximum\Vimeet\Domain\Repository\Participant;

use Proximum\Vimeet\Domain\Model\Participant\Type;
use Proximum\Vimeet\Domain\Model\Participant\TypeView;

interface TypeRepositoryInterface
{
    /**
     * @param integer $eventId
     * @param string  $locale
     *
     * @return TypeView[]
     */
    public function getTypeViewsByEvent($eventId, $locale);

    /**
     * @param integer $typeId
     * @param string $locale
     *
     * @return TypeView
     */
    public function getTypeViewById($typeId, $locale);

    /**
     * @param integer $id
     *
     * @return Type
     */
    public function getById($id);
}
