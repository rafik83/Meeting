<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\RawUserDataToUserInformationViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View\UserInformationView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class UserInformationGetter
{
    /** @var LoginHandler */
    private $loginHandler;

    /** @var RawUserDataToUserInformationViewConverter */
    private $rawUserDataToUserInformationViewConverter;

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var string */
    private $comexposiumGetUserEndpoint;

    /**
     * @param LoginHandler                              $loginHandler
     * @param RawUserDataToUserInformationViewConverter $rawUserDataToUserInformationViewConverter
     * @param HttpAdapterInterface                      $httpAdapter
     * @param ExtraParameterRepositoryInterface         $extraParameterRepository
     * @param string                                    $comexposiumGetUserEndpoint
     */
    public function __construct(
        LoginHandler $loginHandler,
        RawUserDataToUserInformationViewConverter $rawUserDataToUserInformationViewConverter,
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        string $comexposiumGetUserEndpoint
    ) {
        $this->loginHandler = $loginHandler;
        $this->rawUserDataToUserInformationViewConverter = $rawUserDataToUserInformationViewConverter;
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->comexposiumGetUserEndpoint = $comexposiumGetUserEndpoint;
    }

    /**
     * @param Event  $event
     * @param string $email
     * @param string $locale
     *
     * @return null|UserInformationView
     */
    public function handle(Event $event, string $email, string $locale): ?UserInformationView
    {
        $jwtToken = $this->loginHandler->loginAndGetJwtToken();

        if (null === $jwtToken) {
            throw new \LogicException('Can not login to Comexposium');
        }

        $ssoApplicationExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_COMEXPOSIUM_SSO_APPLICATION
        );

        if (!$ssoApplicationExtraParameter instanceof Event\ExtraParameter) {
            throw new \LogicException('Comexposium application parameter is needed');
        }

        $ssoApplication = $ssoApplicationExtraParameter->getValue();

        $endpoint = strtr($this->comexposiumGetUserEndpoint, ['%email%' => $email]);

        try {
            $response = $this->httpAdapter->get(
                $endpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', $jwtToken),
                    'query' => [
                        'appToken' => $ssoApplication
                    ]
                ]
            );

            if ($response->statusCode !== 200) {
                return null;
            }

            $body = json_decode($response->body, true);

            if (!isset($body['result']['data']['profileData'])) {
                return null;
            }

            $data = $body['result']['data']['profileData'];

            if (!\is_array($data)) {
                return null;
            }

            return $this->rawUserDataToUserInformationViewConverter->convert($email, $locale, $data);
        } catch (ServerErrorException $serverErrorException) {
            return null;
        }
    }
}
