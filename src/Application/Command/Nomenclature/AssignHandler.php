<?php

namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;

class AssignHandler
{
    /**
     * @var NomenclatureCloner
     */
    private $nomenclatureCloner;

    /**
     * AssignHandler constructor.
     *
     * @param NomenclatureCloner $nomenclatureCloner
     */
    public function __construct(NomenclatureCloner $nomenclatureCloner)
    {
        $this->nomenclatureCloner = $nomenclatureCloner;
    }

    /**
     * @var Assign
     *
     * @return AssignResult
     */
    public function handle(Assign $command)
    {
        $nomenclature = $this->nomenclatureCloner->duplicate($command->nomenclature, $command->event);

        return new AssignResult($nomenclature);
    }
}
