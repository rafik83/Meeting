<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\LocaleConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class CreateHandler
{
    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var string */
    private $comexposiumSsoCreateUserEndPoint;

    /** @var LocaleConverter */
    private $localeConverter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        string $comexposiumSsoCreateUserEndPoint,
        LocaleConverter $localeConverter
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->comexposiumSsoCreateUserEndPoint = $comexposiumSsoCreateUserEndPoint;
        $this->localeConverter = $localeConverter;
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function handle(Create $command): void
    {
        $salon = $this->extraParameterRepository->findByEventAndType($command->event, Type::TYPE_COMEXPOSIUM_SSO_SALON);
        $sessionSalon = $this->extraParameterRepository->findByEventAndType($command->event, Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON);
        $application = $this->extraParameterRepository->findByEventAndType($command->event, Type::TYPE_COMEXPOSIUM_SSO_APPLICATION);

        if ($salon === null || $sessionSalon === null || $application === null) {
            return;
        }

        /**
         * According to the documentation of the Comexposium SSO Create User api, the required payload is a json:
         * {
         *   "email": "my@email.com",
         *   "fromSalon": "cmxp_event_name",
         *   "fromSessionSalon": "cmxp_event_name_2017",
         *   "language": "fre-FR",
         *   "fromThirdParty": "aaaaabbbbbbbcccccccddddddd"
         * }
         *
         * the fromThirdParty parameter corresponds to the TYPE_COMEXPOSIUM_SSO_APPLICATION extra parameter
         */
        $payload = [
            'email' => $command->email,
            'fromSalon' => $salon->getValue(),
            'fromSessionSalon' => $sessionSalon->getValue(),
            'language' => $this->localeConverter->formatLocale($command->locale),
            'fromThirdParty' => $application->getValue(),
        ];

        try {
            $this->httpAdapter->post($this->comexposiumSsoCreateUserEndPoint, [], json_encode($payload));
        } catch (ServerErrorException $exception) {
            return;
        }
    }
}
