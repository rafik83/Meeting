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
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

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

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param UserRepositoryInterface           $userRepository
     * @param SheetRepositoryInterface          $sheetRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param null|string                       $comexposiumSSOLoaderLibEndpoint
     */
    public function __construct(
        ExtraParameterRepositoryInterface $extraParameterRepository,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        ?string $comexposiumSSOLoaderLibEndpoint
    ) {
        $this->extraParameterRepository = $extraParameterRepository;
        $this->comexposiumSSOLoaderLibEndpoint = $comexposiumSSOLoaderLibEndpoint;
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->extraDataRepository = $extraDataRepository;
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

        $user = $this->userRepository->findByEmail($query->email);

        if ($user !== null) {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $query->event);

            if (!empty($sheets)) {
                $extraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
                    $query->event,
                    ExtraDataType::IMPORTED_FROM_COMEXPOSIUM,
                    $user
                );

                if ($extraData === null) {
                    return null;
                }
            }
        }

        return new SSOComexposiumView(
            $salon->getValue(),
            $sessionSalon->getValue(),
            $application->getValue(),
            $query->locale === 'fr' ? 'fre-FR' : 'eng-GB',
            $query->email,
            $this->comexposiumSSOLoaderLibEndpoint
        );
    }
}
