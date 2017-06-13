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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\View\Normalizer\EventUserSchedulesNormalizerView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventUserSchedulesNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const COL_USER_ID             = 'participantId';
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

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    /**
     * @param TranslatorInterface          $translator
     * @param UserRepositoryInterface      $userRepository
     * @param GroupNameResolver            $groupNameResolver
     * @param TypeNameResolver             $typeNameResolver
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     */
    public function __construct(
        TranslatorInterface $translator,
        UserRepositoryInterface $userRepository,
        GroupNameResolver $groupNameResolver,
        TypeNameResolver $typeNameResolver,
        ParticipantPlanningFormatter $participantPlanningFormatter
    ) {
        parent::__construct($translator);

        $this->userRepository               = $userRepository;
        $this->groupNameResolver            = $groupNameResolver;
        $this->typeNameResolver             = $typeNameResolver;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
    }

    /**
     * Normalizes users' schedules for a given event for serialization.
     *
     * {@inheritdoc}
     *
     * @param EventUserSchedulesNormalizerView $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $rawUsers = [];

        $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($object->event);

        foreach ($this->userRepository->findByEvent($object->event) as $user) {
            $rawUsers[] = $this->getUserRawData($user, $object->event);
        }

        $charset         = isset($context['charset']) ? $context['charset'] : Charset::WINDOWS_1252;
        $normalizedUsers = [];

        foreach ($rawUsers as $rawUser) {
            $normalizedUsers[] = $this->normalizeUserRawData($rawUser, $charset);
        }

        return $normalizedUsers;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'csv' === $format && $data instanceof EventUserSchedulesNormalizerView;
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return array Raw data about user's schedule
     */
    private function getUserRawData(User $user, Event $event)
    {
        $userLocale = $event->getAvailableLocale($user->getLocale());
        $gender     = $user->getGender();

        if (!empty($gender)) {
            $gender = $this->translator->trans(sprintf('gender.%s', $gender));
        }

       // $planning = $this->participantPlanningFormatter->formatPlanningFromParticipantWithUnallocated(
       //     $user,
       //     $userLocale
       // );

        return [
            self::COL_USER_ID             => $user->getId(),
            self::COL_COMPANY             => $this->groupNameResolver->resolve($event, $user),
            self::COL_DESCRIPTION         => null,
            self::COL_PARTICIPATION_TYPE  => $this->typeNameResolver->resolve($user, $event, $userLocale),
            self::COL_TITLE               => $gender,
            self::COL_FIRSTNAME           => $user->getFirstName(),
            self::COL_LASTNAME            => $user->getLastName(),
            self::COL_POSITION            => $user->getPosition(),
            self::COL_PHONE_PREFIX        => null,
            self::COL_PHONE_NUMBER        => $user->getPhone(),
            self::COL_EMAIL               => $user->getEmail(),
            self::COL_MOBILE_PHONE_PREFIX => null,
            self::COL_MOBILE_PHONE        => $user->getMobile(),
            self::COL_SCHEDULE            => null,
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
    private function normalizeUserRawData($rawData, $charset = Charset::WINDOWS_1252)
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
