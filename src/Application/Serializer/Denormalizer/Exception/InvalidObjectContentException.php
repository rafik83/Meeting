<?php

namespace Proximum\Vimeet\Application\Serializer\Denormalizer\Exception;

use Proximum\Vimeet\Domain\Template\Validator\Error\ValidatorError;

class InvalidObjectContentException extends \Exception
{
    /**
     * @var ValidatorError
     */
    private $validatorError;

    /**
     * InvalidObjectContentException constructor.
     *
     * @param ValidatorError $validatorError
     */
    public function __construct(ValidatorError $validatorError)
    {
        parent::__construct();

        $this->validatorError = $validatorError;
    }

    /**
     * @return ValidatorError
     */
    public function getValidatorError()
    {
        return $this->validatorError;
    }
}
