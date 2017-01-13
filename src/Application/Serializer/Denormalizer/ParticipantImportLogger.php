<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Denormalizer;

use Proximum\Vimeet\Domain\Template\Validator\Error\ValidatorError;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;

class ParticipantImportLogger
{
    /**
     * @var int
     */
    private $databaseParticipations;

    /**
     * @var int
     */
    private $fileParticipations;

    /**
     * @var int
     */
    private $createdSheets = 0;

    /**
     * @var int
     */
    private $createdUsers = 0;

    /**
     * @var array
     */
    private $errors = [];
    /**
     * @var TranslatorAdapter
     */
    private $translatorAdapter;

    /**
     * ParticipantImportLogger constructor.
     *
     * @param TranslatorAdapter $translatorAdapter
     */
    public function __construct(TranslatorAdapter $translatorAdapter)
    {
        $this->translatorAdapter = $translatorAdapter;
    }

    /**
     * @param array $databaseParticipations
     * @param array $fileParticipations
     */
    public function init(array $databaseParticipations, array $fileParticipations)
    {
        $this->databaseParticipations = $databaseParticipations;
        $this->fileParticipations     = $fileParticipations;
    }

    /**
     * @param int            $row
     * @param ValidatorError $validatorError
     * @param array          $data
     * @param string         $locale
     */
    public function addError($row, ValidatorError $validatorError, array $data, $locale)
    {
        $newError = array_merge([
            $row,
            $this->translatorAdapter->trans($validatorError->getMessage(), [], null, $locale),
        ], $data);

        $this->errors[] = implode(';', $newError);
    }

    public function userImported()
    {
        $this->createdUsers++;
    }

    public function sheetImported()
    {
        $this->createdSheets++;
    }
}
