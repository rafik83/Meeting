<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\NomenclatureContextProxyInterface;

class NomenclatureContext implements Context
{
    /** @var NomenclatureContextProxyInterface */
    private $nomenclatureContextProxy;

    public function __construct(NomenclatureContextProxyInterface $nomenclatureContextProxy)
    {
        $this->nomenclatureContextProxy = $nomenclatureContextProxy;
    }

    /**
     * @Given there is a nomenclature
     */
    public function thereIsANomenclature()
    {
        $this->nomenclatureContextProxy->getNomenclatureManager()->create(null, 'Chiffre d\'Affaires', [
            'staff1' => ['label' => ['fr' => 'NC.', 'en' => 'NC.']],
            'staff2' => ['label' => ['fr' => '0 - 9', 'en' => '0 - 9']],
            'staff3' => ['label' => ['fr' => '10 - 99', 'en' => '10 - 99']],
            'staff4' => ['label' => ['fr' => '100 - 250', 'en' => '100 - 250']],
            'staff5' => ['label' => ['fr' => '500 - 5000', 'en' => '500 - 5000']],
            'staff6' => ['label' => ['fr' => '\> 5000', 'en' => '\> 5000']],
        ]);
    }
}
