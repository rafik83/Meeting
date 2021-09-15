<?php

namespace Proximum\Vimeet\Domain\Template\Validator\Error;

class TelephoneError extends ValidatorError
{
    const MESSAGE = 'validators.admin.sheet.participant_import.telephone.error';

    /**
     * TelephoneError constructor.
     *
     * @param string $data
     * @param bool   $hasNoError
     */
    public function __construct($data, $hasNoError)
    {
        parent::__construct(self::MESSAGE, $data, $hasNoError);
    }
}
