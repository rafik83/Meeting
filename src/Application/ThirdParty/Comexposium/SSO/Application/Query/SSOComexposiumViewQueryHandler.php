<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\View\SSOComexposiumView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\LocaleConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class SSOComexposiumViewQueryHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var null|string */
    private $comexposiumSSOLoaderLibEndpoint;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var LocaleConverter */
    private $localeConverter;

    /**
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param UserRepositoryInterface           $userRepository
     * @param SheetRepositoryInterface          $sheetRepository
     * @param LocaleConverter                   $localeConverter
     * @param null|string                       $comexposiumSSOLoaderLibEndpoint
     */
    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        LocaleConverter $localeConverter,
        ?string $comexposiumSSOLoaderLibEndpoint
    ) {
        $this->extraParameterRepository = $extraParameterRepository;
        $this->comexposiumSSOLoaderLibEndpoint = $comexposiumSSOLoaderLibEndpoint;
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->localeConverter = $localeConverter;
    }

    public function handle(SSOComexposiumViewQuery $query): ?SSOComexposiumView
    {
        $ssoEnabled = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_COMEXPOSIUM_SSO_ENABLED);

        if ($ssoEnabled === null) {
            return null;
        }

        $salon = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_COMEXPOSIUM_SSO_SALON);
        $sessionSalon = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON);
        $application = $this->extraParameterRepository->findByEventAndType($query->event, Type::TYPE_COMEXPOSIUM_SSO_APPLICATION);

        if ($salon === null || $sessionSalon === null || $application === null) {
            return null;
        }

        if (!$this->enableSSOForEmail($query->event, $query->email)) {
            return null;
        }

        return new SSOComexposiumView(
            $salon->getValue(),
            $sessionSalon->getValue(),
            $application->getValue(),
            $this->localeConverter->formatLocale($query->locale),
            $query->email,
            $this->comexposiumSSOLoaderLibEndpoint,
            $query->showLogin
        );
    }

    /**
     * @param Event       $event
     * @param null|string $email
     *
     * @return bool
     */
    private function enableSSOForEmail(Event $event, ?string $email): bool
    {
        if (null === $email) {
            return true;
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user instanceof User) {
            return true;
        }

        $hasSheetImported = false;
        $hasParticipantImported = false;
        $isOwner = false;

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        $hasSheets = !empty($sheets);

        if (!$hasSheets) {
            return true;
        }

        foreach ($sheets as $sheet) {
            if (true === $sheet->isImported()) {
                $hasSheetImported = true;
            }

            $participant = $sheet->getUserParticipant($user);

            if ($participant !== null && $participant->isImported()) {
                $hasParticipantImported = true;
            }

            if ($sheet->isOwner($user)) {
                $isOwner = true;
            }
        }

        // We do not allow SSO for user not imported on a sheet imported
        if ($hasParticipantImported === false && $hasSheetImported === true && $isOwner === false) {
            return false;
        }

        if ($hasSheetImported === false) {
            return false;
        }

        return true;
    }
}
