<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Import;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Infrastructure\Adapter\SessionAdapter;

class ImportMappingViewQueryHandler
{
    /**
     * @var SessionAdapter
     */
    private $session;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var SerializerAdapterInterface
     */
    private $serializerAdapter;

    /**
     * @param SerializerAdapterInterface $serializerAdapter
     * @param SessionAdapter             $session
     * @param TemplateDataFactory        $templateDataFactory
     */
    public function __construct(
        SerializerAdapterInterface $serializerAdapter,
        SessionAdapter $session,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->serializerAdapter   = $serializerAdapter;
        $this->session             = $session;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param ImportMappingViewQuery $query
     *
     * @return ImportMappingView
     * @throws \Exception
     */
    public function handle(ImportMappingViewQuery $query)
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

        $registrationHeaders = [
            ParticipantImportTag::REGISTRATION_FIELD_IGNORE => 'form.participant_import.field.ignore',
            ParticipantImportTag::REGISTRATION_FIELD_MAIL   => 'form.participant_import.field.mail',
        ];

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType($query->type, $query->locale);

        $templateObjects = $registrationTemplate->getParticipantAndSheetDataExceptedImageObject();

        foreach ($templateObjects as $object) {
            if ($object instanceof ContentObjectInterface) {
                $label = $object->getLabel($query->locale);

                if ($label !== null) {
                    $registrationHeaders[$object->getKey()] = $label;
                }
            }
        }

        return new ImportMappingView($csvHeaders, $registrationHeaders);
    }
}
