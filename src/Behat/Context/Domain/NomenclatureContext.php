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
     * @Given there is a turnover nomenclature
     */
    public function thereIsATurnoverNomenclature()
    {
        $event = $this->nomenclatureContextProxy->getStorage()->get('event');

        $nomenclature = $this->nomenclatureContextProxy->getNomenclatureManager()->create($event, 'Chiffre d\'Affaires', [
            'turnover1' => ['label' => ['fr' => 'NC.', 'en' => 'NC.']],
            'turnover2' => ['label' => ['fr' => '<50k€', 'en' => '<50k€']],
            'turnover3' => ['label' => ['fr' => '50k€ - 500k€', 'en' => '50k€ - 500k€']],
            'turnover4' => ['label' => ['fr' => '500k€ - 1M€', 'en' => '500k€ - 1M€']],
            'turnover5' => ['label' => ['fr' => '>1M€', 'en' => '>1M€']],
        ]);

        $this->addToStorage('turnover', $nomenclature->getId());

        if ($event) {
            $this->nomenclatureContextProxy
                ->getNomenclatureManager()
                ->createTaggedNomenclatureFilter($event, 'sheet_organization_turnover', $nomenclature->getId());
        }
    }

    /**
     * @Given there is a position nomenclature
     */
    public function thereIsAPositionNomenclature()
    {
        $event = $this->nomenclatureContextProxy->getStorage()->get('event');

        $nomenclature = $this->nomenclatureContextProxy->getNomenclatureManager()->create($event, 'Autres', [
            'position94' => ['label' => ['fr' => 'Avocat', 'en' => 'Lawyer']],
            'position95' => ['label' => ['fr' => 'Chargé de mission', 'en' => 'Project Manager']],
            'position96' => ['label' => ['fr' => 'Chargé d\'études', 'en' => 'Research officer']],
            'position97' => ['label' => ['fr' => 'Conférencier', 'en' => 'Speaker']],
            'position98' => ['label' => ['fr' => 'Consultant', 'en' => 'Consultant']],
        ]);

        $this->addToStorage('position', $nomenclature->getId());

        if ($event) {
            $this->nomenclatureContextProxy
                ->getNomenclatureManager()
                ->createTaggedNomenclatureFilter($event, 'participant_position', $nomenclature->getId());
        }
    }

    private function addToStorage(string $name, int $id)
    {
        $nomenclatures = $this->nomenclatureContextProxy->getStorage()->get('nomenclatures');
        if (!$nomenclatures) {
            $nomenclatures = [];
        }

        $nomenclatures[$name] = $id;

        $this->nomenclatureContextProxy->getStorage()->set('nomenclatures', $nomenclatures);
    }
}
