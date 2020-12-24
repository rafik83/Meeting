<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdate;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdateHandler;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\RawUserDataToUserInformationViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View\UserInformationView;
use Proximum\Vimeet\Domain\Event\ExtraData\Type as EventExtraDataType;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
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

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var AddOrUpdateHandler */
    private $addOrUpdateHandler;

    /** @var string */
    private $comexposiumGetUserEndpoint;

    public function __construct(
        LoginHandler $loginHandler,
        RawUserDataToUserInformationViewConverter $rawUserDataToUserInformationViewConverter,
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        AddOrUpdateHandler $addOrUpdateHandler,
        string $comexposiumGetUserEndpoint
    ) {
        $this->loginHandler = $loginHandler;
        $this->rawUserDataToUserInformationViewConverter = $rawUserDataToUserInformationViewConverter;
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->addOrUpdateHandler = $addOrUpdateHandler;
        $this->comexposiumGetUserEndpoint = $comexposiumGetUserEndpoint;
    }

    /**
     * @param Event  $event
     * @param string $email
     * @param string $locale
     *
     * @throws \LogicException
     *
     * @return null|UserInformationView
     */
    public function handle(Event $event, string $email, string $locale): ?UserInformationView
    {
        $ssoApplicationExtraParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_COMEXPOSIUM_SSO_APPLICATION
        );

        if (!$ssoApplicationExtraParameter instanceof Event\ExtraParameter) {
            throw new \LogicException('Comexposium application parameter is needed');
        }

        $ssoApplication = $ssoApplicationExtraParameter->getValue();

        $endpoint = strtr($this->comexposiumGetUserEndpoint, ['%email%' => $email]);

        $jwtToken = $this->getSavedJwtToken($event);

        if (null === $jwtToken) {
            $jwtToken = $this->getAndSaveJwtTokenByApi($event);
        }

        return $this->getUserInformation($event, $endpoint, $jwtToken, $ssoApplication, $email, $locale, true);
    }

    /**
     * @param Event  $event
     * @param string $endpoint
     * @param string $jwtToken
     * @param string $ssoApplication
     * @param string $email
     * @param string $locale
     * @param bool   $retry
     *
     * @throws \LogicException
     *
     * @return null|UserInformationView
     */
    private function getUserInformation(
        Event $event,
        string $endpoint,
        string $jwtToken,
        string $ssoApplication,
        string $email,
        string $locale,
        bool $retry
    ): ?UserInformationView {
        try {
            $response = $this->httpAdapter->get(
                $endpoint,
                [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'Authorization' => sprintf('Bearer %s', $jwtToken),
                    'query' => [
                        'appToken' => $ssoApplication,
                    ],
                ]
            );

            if (401 === $response->statusCode) {
                if (!$retry) {
                    throw new \LogicException(
                        sprintf('Can not get user information from Comexposium for email : %s', $email)
                    );
                }

                $jwtToken = $this->getAndSaveJwtTokenByApi($event);

                return $this->getUserInformation($event, $endpoint, $jwtToken, $ssoApplication, $email, $locale, false);
            }

            if (200 !== $response->statusCode) {
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

    /**
     * @param Event $event
     *
     * @throws \LogicException
     *
     * @return null|string
     */
    private function getSavedJwtToken(Event $event): ?string
    {
        $eventExtraData = $this->extraDataRepository->getExtraDataForEvent(
            $event,
            EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN
        );

        if ($eventExtraData instanceof Event\ExtraData) {
            return $eventExtraData->getValue();
        }

        return null;
    }

    /**
     * @param Event $event
     *
     * @throws \LogicException
     *
     * @return string
     */
    private function getAndSaveJwtTokenByApi(Event $event): string
    {
        $jwtToken = $this->loginHandler->loginAndGetJwtToken();

        if (null === $jwtToken) {
            throw new \LogicException('Can not login to Comexposium');
        }

        $this->addOrUpdateHandler->handle(
            new AddOrUpdate($event, EventExtraDataType::COMEXPOSIUM_SSO_JWT_TOKEN, $jwtToken)
        );

        return $jwtToken;
    }
}
