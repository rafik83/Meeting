<?php

namespace Proximum\Vimeet\Application\Serializer\Denormalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\Validator\Error\ValidatorError;

class ParticipantImportLogger
{
    public const EXISTING_PARTICIPATIONS = 'existing_participations';
    public const FILE_PARTICIPATIONS = 'file_participations';
    public const CREATED_SHEETS = 'created_sheets';
    public const CREATED_USERS = 'created_users';
    public const IMPORT_ERRORS = 'import_errors';
    public const PARTICIPANT_IMPORT_ID = 'participant_import_id';

    /** @var int */
    private $existingParticipations = 0;

    /** @var int */
    private $fileParticipations = 0;

    /** @var int */
    private $createdSheets = 0;

    /** @var int */
    private $createdUsers = 0;

    /** @var array */
    private $errors = [];

    /** @var TranslatorInterface */
    private $translatorAdapter;

    /** @var Sheet[] */
    private $sheets = [];

    /** @var array */
    private $emails = [];

    public function __construct(TranslatorInterface $translatorAdapter)
    {
        $this->translatorAdapter = $translatorAdapter;
    }

    public function init(int $fileParticipations): void
    {
        $this->fileParticipations = $fileParticipations;
    }

    /**
     * @param int            $row
     * @param ValidatorError $validatorError
     * @param array|string   $data
     * @param string         $locale
     */
    public function addError($row, ValidatorError $validatorError, $data, $locale): void
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

    public function userImported(User $user): void
    {
        $this->emails[] = mb_strtolower($user->getEmail());
        ++$this->createdUsers;
    }

    public function sheetImported(Sheet $sheet): void
    {
        $this->sheets[] = $sheet;
        ++$this->createdSheets;
    }

    public function existingParticipations(): void
    {
        ++$this->existingParticipations;
    }

    /**
     * @param $fileEmail
     *
     * @return bool
     */
    public function isImported($fileEmail): bool
    {
        return in_array(mb_strtolower($fileEmail), $this->emails);
    }

    public function toArray(): array
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
    public function getSheets(): array
    {
        return $this->sheets;
    }
}
