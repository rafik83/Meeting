<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Template\Exception\MissingRequiredDataException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;

class TemplateDataValidator
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * Validator constructor.
     *
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Participant $participant
     * @param int         $block
     * @param string      $locale
     * @param array       $data
     *
     * @throws MissingRequiredDataException
     */
    public function validateParticipantData(Participant $participant, $block, $locale, array $data)
    {
        $objects = $this->templateDataFactory
            ->createRegistrationFromParticipant($participant, $locale)
            ->getBlock(intval($block))
            ->getObjects();

        $this->validateData($objects, $data);
    }

    /**
     * @param Type   $type
     * @param string $locale
     * @param array  $data
     *
     * @throws MissingRequiredDataException
     */
    public function validateFirstParticipantDataFromType(Type $type, $locale, array $data)
    {
        $objects = $this->templateDataFactory
            ->createRegistrationFromType($type, $locale)
            ->getFirstBlock()
            ->getObjects();

        $this->validateData($objects, $data);
    }

    /**
     * @param Object[] $objects
     * @param array    $data
     *
     * @throws MissingRequiredDataException
     */
    private function validateData(array $objects, array $data)
    {
        $missingRequiredKeys = [];

        foreach ($objects as $key => $object) {
            if (!$object->missingRequiredData($data)) {
                $missingRequiredKeys[] = $key;
            }
        }

        if (!empty($missingRequiredKeys)) {
            throw new MissingRequiredDataException($missingRequiredKeys);
        }
    }
}
