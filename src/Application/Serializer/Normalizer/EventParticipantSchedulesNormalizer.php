<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\View\Normalizer\EventParticipantSchedulesNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventParticipantSchedulesNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_USER_ID             = 'userId';
    const COL_TITLE               = 'title'; // Gender
    const COL_FIRSTNAME           = 'firstName';
    const COL_LASTNAME            = 'lastName';
    const COL_COMPANY             = 'companyName';
    const COL_PARTICIPATION_TYPE  = 'type';
    const COL_DESCRIPTION         = 'description';
    const COL_POSITION            = 'position';
    const COL_PHONE_PREFIX        = 'phonePrefix';
    const COL_PHONE_NUMBER        = 'phoneNumber';
    const COL_EMAIL               = 'email';
    const COL_MOBILE_PHONE_PREFIX = 'mobilePhonePrefix';
    const COL_MOBILE_PHONE        = 'mobilePhone';
    const COL_SCHEDULE            = 'planning';

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var GroupNameResolver
     */
    private $groupNameResolver;

    /**
     * @var ParticipantPlanningFormatter
     */
    private $participantPlanningFormatter;

    /**
     * @param TranslatorInterface            $translator
     * @param ParticipantRepositoryInterface $participantRepository
     * @param GroupNameResolver              $groupNameResolver
     * @param ParticipantPlanningFormatter   $participantPlanningFormatter
     */
    public function __construct(
        TranslatorInterface $translator,
        ParticipantRepositoryInterface $participantRepository,
        GroupNameResolver $groupNameResolver,
        ParticipantPlanningFormatter $participantPlanningFormatter
    ) {
        parent::__construct($translator);

        $this->participantRepository        = $participantRepository;
        $this->groupNameResolver            = $groupNameResolver;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
    }

    /**
     * Normalizes participants' schedules for a given event for serialization.
     *
     * {@inheritdoc}
     *
     * @param EventParticipantSchedulesNormalizerView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $rawParticipants = [];
        $locale          = $context['locale'];
        $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($object->event);

        foreach ($this->participantRepository->getParticipantsByEvent($object->event, $locale) as $participant) {
            $rawParticipants[] = $this->getParticipantRawData($participant, $object->admin);
        }

        $charset                = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;
        $normalizedParticipants = [];

        foreach ($rawParticipants as $rawParticipant) {
            $normalizedParticipants[] = $this->normalizeParticipantRawData($rawParticipant, $charset);
        }

        return $normalizedParticipants;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'csv' === $format && $data instanceof EventParticipantSchedulesNormalizerView;
    }

    /**
     * @param Participant $participant
     * @param Admin       $admin
     *
     * @return array Raw data about participant's schedule
     */
    private function getParticipantRawData(Participant $participant, Admin $admin)
    {
        $sheet             = $participant->getSheet();
        $user              = $participant->getUser();
        $event             = $sheet->getEvent();
        $participantLocale = $event->getAvailableLocale($user->getLocale());
        $adminLocale       = $event->getAvailableLocale($admin->getLocale());

        $gender = $user->getGender();

        if (null !== $gender && !empty($gender)) {
            $gender = $this->translator->trans(sprintf('gender.%s', $gender));
        }

        $planning = $this->participantPlanningFormatter->formatPlanningFromParticipantWithUnallocated(
            $participant,
            $participantLocale
        );

        return [
            self::COL_USER_ID             => sprintf("%s-%s", $sheet->getId(), $user->getId()),
            self::COL_COMPANY             => $this->groupNameResolver->resolve($event, $user),
            self::COL_DESCRIPTION         => null,
            self::COL_PARTICIPATION_TYPE  => $sheet->getType()->getTitle($adminLocale),
            self::COL_TITLE               => $gender,
            self::COL_FIRSTNAME           => $user->getFirstName(),
            self::COL_LASTNAME            => $user->getLastName(),
            self::COL_POSITION            => $user->getPosition(),
            self::COL_PHONE_PREFIX        => null,
            self::COL_PHONE_NUMBER        => $user->getPhone(),
            self::COL_EMAIL               => $user->getEmail(),
            self::COL_MOBILE_PHONE_PREFIX => null,
            self::COL_MOBILE_PHONE        => $user->getMobile(),
            self::COL_SCHEDULE            => $planning,
        ];
    }

    /**
     * Returns an array of normalized data from a participant's schedule raw data
     * (normalizing includes charset encoding, string substitution for boolean values, etc.)
     *
     * @param array  $rawData
     * @param string $charset
     *
     * @return array
     */
    private function normalizeParticipantRawData($rawData, $charset = Charset::WINDOWS_1252)
    {
        $normalizedData = [];

        foreach ($rawData as $fieldKey => $input) {
            $normalizedData[$fieldKey] = $this->convertCharset(
                $input,
                Charset::UTF_8,
                $charset
            );
        }

        return $normalizedData;
    }

}
