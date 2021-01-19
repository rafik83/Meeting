<?php

namespace Proximum\Vimeet\Domain\Template\Validator\Error;

class CountryError extends ValidatorError
{
    const MESSAGE = 'validators.admin.sheet.import_participant.error.country';

    /**
     * CountryError constructor.
     *
     * @param string $data
     * @param bool   $hasNoError
     */
    public function __construct($data, $hasNoError)
    {
        parent::__construct(self::MESSAGE, $data, $hasNoError);
    }
}
