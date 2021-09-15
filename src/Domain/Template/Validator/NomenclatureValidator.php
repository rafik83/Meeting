<?php

namespace Proximum\Vimeet\Domain\Template\Validator;

use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\Validator\Error\NomenclatureError;

class NomenclatureValidator implements ObjectValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($data, array $options = [])
    {
        if (empty($data)) {
            return new NomenclatureError($data, true);
        }

        $dataItems = explode(';', $data);
        $dataItems = array_map(function ($element) {
            return str_replace(Nomenclature::SEMICOLON_ESCAPE_CHAR, ';', $element);
        }, $dataItems);

        /** @var Nomenclature $nomenclature */
        $nomenclature = $options['object'];
        $items        = $nomenclature->getNomenclatureModel()->getLastLevel();
        $dataItemFound = 0;
        $dataItemToFound = \count($dataItems);

        foreach ($items as $nomenclatureItem) {
            $nomenclatureLabel = $nomenclatureItem->getLabel($options['locale']);

            if (\in_array($nomenclatureLabel, $dataItems, true)) {
                ++$dataItemFound;
            }
        }

        return new NomenclatureError($data, $dataItemFound === $dataItemToFound);
    }
}
