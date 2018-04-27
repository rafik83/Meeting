<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Denormalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\Validator\Error\ValidatorError;

class ParticipantImportLogger
{
    const EXISTING_PARTICIPATIONS = 'existing_participations';
    const FILE_PARTICIPATIONS     = 'file_participations';
    const CREATED_SHEETS          = 'created_sheets';
    const CREATED_USERS           = 'created_users';
    const IMPORT_ERRORS           = 'import_errors';
    const PARTICIPANT_IMPORT_ID   = 'participant_import_id';

    /**
     * @var int
     */
    private $existingParticipations = 0;

    /**
     * @var int
     */
    private $fileParticipations = 0;

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
     * @var TranslatorInterface
     */
    private $translatorAdapter;

    /**
     * @var Sheet[]
     */
    private $sheets = [];

    /**
     * @var array
     */
    private $emails = [];

    /**
     * @param TranslatorInterface $translatorAdapter
     */
    public function __construct(TranslatorInterface $translatorAdapter)
    {
        $this->translatorAdapter = $translatorAdapter;
    }

    /**
     * @param int $fileParticipations
     */
    public function init($fileParticipations)
    {
        $this->fileParticipations = $fileParticipations;
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
            $row + 2, // Because of array index and first line deletion, +2 to retrieve the good csv line number
            $this->translatorAdapter->trans($validatorError->getMessage(), [], 'validators', $locale),
        ];

        if (is_array($data)) {
            $newError = array_merge($newError, $data);
        } else {
            $newError[] = $data;
        }

        $this->errors[] = implode(';', $newError);
    }

    /**
     * @param User $user
     */
    public function userImported(User $user)
    {
        $this->emails[] = mb_strtolower($user->getEmail());
        ++$this->createdUsers;
    }

    /**
     * @param Sheet $sheet
     */
    public function sheetImported(Sheet $sheet)
    {
        $this->sheets[] = $sheet;
        ++$this->createdSheets;
    }

    public function existingParticipations()
    {
        ++$this->existingParticipations;
    }

    /**
     * @param $fileEmail
     *
     * @return bool
     */
    public function isImported($fileEmail)
    {
        return in_array(mb_strtolower($fileEmail), $this->emails);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::EXISTING_PARTICIPATIONS => $this->existingParticipations,
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
