<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Symfony\Component\Form\DataTransformerInterface;

abstract class AbstractTransformer implements DataTransformerInterface
{
    /**
     * @var Nomenclature
     */
    protected $nomenclature;

    /**
     * KeyToNomenclatureItemTransformer constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }

    /**
     * @param NomenclatureItem[] $items
     * @param string             $key
     *
     * @return NomenclatureItem|null
     */
    protected static function findByKey(array $items, $key)
    {
        foreach ($items as $item) {
            if ($item->getKey() === $key) {
                return $item;
            }
        }

        return null;
    }
}
