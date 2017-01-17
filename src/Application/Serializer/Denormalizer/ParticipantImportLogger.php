<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Denormalizer;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\Validator\Error\ValidatorError;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;

class ParticipantImportLogger
{
    const DATABASE_PARTICIPATIONS = 'data_participations';
    const FILE_PARTICIPATIONS     = 'file_participations';
    const CREATED_SHEETS          = 'created_sheets';
    const CREATED_USERS           = 'created_users';
    const IMPORT_ERRORS           = 'import_errors';
    const SESSION_FLASH           = 'session_flash';
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
     * @var Sheet[] $sheets
     */
    private $sheets = [];

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
     * @param int $databaseParticipations
     * @param int $fileParticipations
     */
    public function init($databaseParticipations, $fileParticipations)
    {
        $this->databaseParticipations = $databaseParticipations;
        $this->fileParticipations     = $fileParticipations;
    }

    /**
     * @param int            $row
     * @param ValidatorError $validatorError
     * @param array|string   $data
     * @param string         $locale
     */
    public function addError($row, ValidatorError $validatorError, $data, $locale)
    {
        $newError = [
            $row,
            $this->translatorAdapter->trans($validatorError->getMessage(), [], 'validators', $locale),
        ];

        if (is_array($data)) {
            $newError = array_merge($newError, $data);
        } else {
            $newError[] = $data;
        }

        $this->errors[] = implode(';', $newError);
    }

    public function userImported()
    {
        $this->createdUsers++;
    }

    /**
     * @param $sheet
     */
    public function sheetImported(Sheet $sheet)
    {
        $this->sheets[] = $sheet;
        $this->createdSheets++;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::DATABASE_PARTICIPATIONS => $this->databaseParticipations,
            self::FILE_PARTICIPATIONS     => $this->fileParticipations,
            self::CREATED_SHEETS          => $this->createdSheets,
            self::CREATED_USERS           => $this->createdUsers,
            self::IMPORT_ERRORS           => $this->errors,
        ];
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets;
    }
}
