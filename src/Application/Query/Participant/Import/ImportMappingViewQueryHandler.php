<?php

namespace Proximum\Vimeet\Application\Query\Participant\Import;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Domain\Repository\Sheet\ImportMappingRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Infrastructure\Adapter\SessionAdapter;

class ImportMappingViewQueryHandler
{
    private const HEADER_REGISTRATION = 'admin.participant_import.header.registration';
    private const HEADER_SHEET = 'admin.participant_import.header.sheet';

    /** @var SessionAdapter */
    private $session;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ImportMappingRepositoryInterface */
    private $importMappingRepository;

    public function __construct(
        ImportMappingRepositoryInterface $importMappingRepository,
        SerializerAdapterInterface $serializerAdapter,
        SessionAdapter $session,
        TemplateDataFactory $templateDataFactory,
        TranslatorInterface $translator
    ) {
        $this->importMappingRepository = $importMappingRepository;
        $this->serializerAdapter = $serializerAdapter;
        $this->session = $session;
        $this->templateDataFactory = $templateDataFactory;
        $this->translator = $translator;
    }

    /**
     * @param ImportMappingViewQuery $query
     *
     * @throws \Exception
     *
     * @return ImportMappingView
     */
    public function handle(ImportMappingViewQuery $query): ImportMappingView
    {
        $data = $this->serializerAdapter->decode(
            file_get_contents($this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE)),
            'csv',
            ['csv_delimiter' => ';']
        );

        if (!isset($data[0]) || !is_array($data[0])) {
            throw new \Exception('No header found');
        }

        $csvHeaders = array_keys($data[0]);

        $headers = [
            ParticipantImportTag::REGISTRATION_FIELD_IGNORE => 'form.participant_import.field.ignore',
            ParticipantImportTag::REGISTRATION_FIELD_MAIL => 'form.participant_import.field.mail',
            ParticipantImportTag::REGISTRATION_FIELD_LOCALE => 'form.participant_import.field.locale',
        ];

        $allowMultiSheet = $this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_ALLOW_MULTI_SHEET) ?? false;
        if ($allowMultiSheet) {
            $headers[ParticipantImportTag::FIELD_GROUP_TITLE] = 'form.participant_import.field.group_title';
        }

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType($query->type, $query->locale);
        $templateObjects = $registrationTemplate->getParticipantAndSheetDataExceptedImageObject();

        foreach ($templateObjects as $object) {
            if ($object instanceof ContentObjectInterface) {
                $label = $object->getLabel($query->locale);

                if (null !== $label) {
                    $headers[$object->getKey()] = sprintf(
                        '%s%s',
                        $this->translator->trans(self::HEADER_REGISTRATION, [], 'messages', $query->locale),
                        $label
                    );
                }
            }
        }

        $sheetTemplate = $this->templateDataFactory->createSheetTemplateFromType($query->type, $query->locale);
        $templateObjects = $sheetTemplate->getEditableTextAndNomenclatureObjects();

        foreach ($templateObjects as $object) {
            if ($object instanceof ContentObjectInterface) {
                $label = $object->getLabel($query->locale);

                if (null !== $label) {
                    $headers[$object->getKey()] = sprintf(
                        '%s%s',
                        $this->translator->trans(self::HEADER_SHEET, [], 'messages', $query->locale),
                        $label
                    );
                }
            }
        }

        $savedImportMapping = null;
        $savedImportMappingId = $this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_SAVED_MAPPING);

        if ($savedImportMappingId) {
            $savedImportMapping = $this->importMappingRepository->getById($savedImportMappingId);
        }

        return new ImportMappingView($csvHeaders, $headers, $allowMultiSheet, $savedImportMapping);
    }
}
