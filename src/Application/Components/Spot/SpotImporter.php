<?php

namespace Proximum\Vimeet\Application\Components\Spot;

use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Application\Exception\Spot\Import\InvalidImportHeaderFileFormatException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\Import;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\ValidatorAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;
use Symfony\Component\Validator\ConstraintViolation;

class SpotImporter
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ValidatorInterface */
    private $validatorAdapter;

    /** @var TranslatorAdapter */
    private $translatorAdapter;

    /*** @var SerializerAdapter */
    private $serializerAdapter;

    /** @var string */
    private $importDir;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param ValidatorAdapter         $validatorAdapter
     * @param TranslatorAdapter        $translatorAdapter
     * @param SerializerAdapter        $serializerAdapter
     * @param string                   $importDir
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ValidatorAdapter $validatorAdapter,
        TranslatorAdapter $translatorAdapter,
        SerializerAdapter $serializerAdapter,
        string $importDir
    ) {
        $this->importDir = $importDir;
        $this->sheetRepository = $sheetRepository;
        $this->validatorAdapter = $validatorAdapter;
        $this->translatorAdapter = $translatorAdapter;
        $this->serializerAdapter = $serializerAdapter;
    }

    /**
     * @param Event  $event
     * @param File   $spotImportedFile
     * @param string $locale
     *
     * @throws InvalidImportHeaderFileFormatException
     *
     * @return Import[]
     */
    public function import(Event $event, File $spotImportedFile, string $locale): array
    {
        $spotImports = $this->serializerAdapter->deserialize(
            file_get_contents($this->importDir . $spotImportedFile->getPath()),
            Import::class,
            'csv',
            [
                'csv_delimiter' => ';',
                'event' => $event,
            ]
        );

        $sheetsHasSpot = [];
        $referenceImported = [];

        /** @var Import $spotImport */
        foreach ($spotImports as $spotImport) {
            if (null === $spotImport->spot) {
                continue;
            }

            if (isset($referenceImported[strtolower($spotImport->spot->getReference())])) {
                $spotImport->addError($this->translateError('validators.spot.reference.affected', $locale));
            }

            $referenceImported[strtolower($spotImport->spot->getReference())] = true;

            $validations = $this->validatorAdapter->validate($spotImport->spot);

            /** @var ConstraintViolation $validation */
            foreach ($validations as $validation) {
                $spotImport->addError($validation->getMessage());
            }

            foreach ($spotImport->sheetIds as $sheetId) {
                $sheetId = (int) $sheetId;

                if ($sheetId < 1) {
                    $spotImport->addError($this->translateError(
                        'validators.spot.sheet.invalid_format',
                        $locale,
                        ['%sheetId%' => $sheetId]
                    ));
                    continue;
                }

                if (isset($sheetsHasSpot[$sheetId])) {
                    $spotImport->addError($this->translateError(
                        'validators.spot.sheet.already_imported',
                        $locale,
                        ['%sheetId%' => $sheetId]
                    ));
                    continue;
                }

                $sheetView = $this->sheetRepository->getSheetViewByEventAndId($event, $sheetId);

                if (null === $sheetView) {
                    $spotImport->addError($this->translateError(
                        'validators.spot.sheet.not_exist',
                        $locale,
                        ['%sheetId%' => $sheetId]
                    ));
                    continue;
                }

                $sheetsHasSpot[$sheetId] = true;
                $spotImport->addSheetView($sheetView);
            }
        }

        return $spotImports;
    }

    /**
     * @param string $translationKey
     * @param string $locale
     * @param array  $parameters
     *
     * @return string
     */
    public function translateError(string $translationKey, string $locale, array $parameters = []): string
    {
        return  $this->translatorAdapter->trans($translationKey, $parameters, 'validators', $locale);
    }
}
