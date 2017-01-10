<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Import;

use Proximum\Vimeet\Application\Command\Participant\Import;
use Proximum\Vimeet\Application\Serializer\Decoder\CsvDecoder;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Application\View\Participant\MappingView;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Adapter\SessionAdapter;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class ImportMappingViewQueryHandler
{
    /**
     * @var SessionAdapter
     */
    private $session;

    /**
     * @var DecoderInterface
     */
    private $csvDecoder;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * ImportMappingViewQueryHandler constructor.
     *
     * @param CsvDecoder          $csvDecoder
     * @param SessionAdapter      $session
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(
        CsvDecoder $csvDecoder,
        SessionAdapter $session,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->session             = $session;
        $this->csvDecoder          = $csvDecoder;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param ImportMappingViewQuery $query
     *
     * @return ImportMappingView
     */
    public function handle(ImportMappingViewQuery $query)
    {
        $csvHeaders          = $this->csvDecoder->decodeHeaders($this->session->get(Import::PARTICIPANT_IMPORT_FILE));
        $registrationHeaders = [];

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType($query->type, $query->locale);

        $templateObjects = $registrationTemplate->getObjects();

        foreach ($templateObjects as $object) {
            $label                 = $object->getLabel($query->locale);
            $registrationHeaders[] = $label;
        }

        return new ImportMappingView($csvHeaders, $registrationHeaders);
    }
}
