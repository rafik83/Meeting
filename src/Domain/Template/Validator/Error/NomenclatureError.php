<?php

namespace Proximum\Vimeet\Domain\Template\Validator\Error;

class NomenclatureError extends ValidatorError
{
    const MESSAGE = 'validators.admin.sheet.participant_import.nomenclature.error';

    /**
     * NomenclatureError constructor.
     *
     * @param string $data
     * @param bool   $hasNoError
     */
    public function __construct($data, $hasNoError)
    {
        parent::__construct(self::MESSAGE, $data, $hasNoError);
    }
}
