<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Denormalizer;

use Proximum\Vimeet\Application\Components\Import\MappingGuesser;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ParticipantDenormalizer implements DenormalizerInterface
{
    const FORMAT = 'csv';

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * ParticipantDenormalizer constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param TemplateDataFactory            $templateDataFactory
     * @param Synchronizer                   $synchronizer
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        TemplateDataFactory $templateDataFactory,
        Synchronizer $synchronizer,
        \DateTimeInterface $dateTime
    ) {
        $this->participantRepository = $participantRepository;
        $this->templateDataFactory   = $templateDataFactory;
        $this->synchronizer          = $synchronizer;
        $this->dateTime              = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $participants = $this->participantRepository->findByEvent($context['event']);

        $mappingGuesser = new MappingGuesser(
            $context['mappings'],
            $context['csvHeaders'],
            $context['registrationHeaders']
        );

        $mappedMailCsvColumn = $mappingGuesser->getMappedInKey(ParticipantImportTag::REGISTRATION_FIELD_MAIL);

        if ($mappedMailCsvColumn === false) {
            throw new \Exception('Mail field not set'); // TODO: Custom exception
        }

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType(
            $context['type'],
            $context['locale']
        );

        foreach ($data as $row) {
            $email = $row[$mappedMailCsvColumn];

            if ($this->isAlreadyRegister($email, $participants)) {
                continue;
            }

            foreach ($row as $key => $column) {
                $registrationObjectKey = $mappingGuesser->getMappedOutKey($key);

                $templateObject = $registrationTemplate->getObject($registrationObjectKey);

                if ($templateObject instanceof ContentObjectInterface) {
                    $templateObject->setContentValue($column);
                }
            }

            $user        = new User($email, null, null, $context['locale']);
            $sheet       = new Sheet($context['event'], $context['type'], [], $user, $this->dateTime);
            $participant = new Participant($sheet, $user, $registrationTemplate->getData(), false);

            $this->participantRepository->add($participant);

            $this->synchronizer->set($registrationTemplate, $user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $format === self::FORMAT;
    }

    /**
     * @param string        $email
     * @param Participant[] $participants
     *
     * @return bool
     */
    private function isAlreadyRegister($email, array $participants)
    {
        foreach ($participants as $participant) {
            if ($participant->getUser()->getEmail() === $email) {
                return true;
            }
        }

        return false;
    }
}
