<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\User;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\LocaleConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class CreateHandler
{
    public const RESPONSE_MISSING_PARAMETERS = 'missing_parameters';
    public const RESPONSE_ERROR = 'error';
    public const RESPONSE_ALREADY_CREATED = 'already_created';
    public const RESPONSE_CREATED = 'created';

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

    public function handle(Create $command): string
    {
        $salon = $this->extraParameterRepository->findByEventAndType($command->event, Type::TYPE_COMEXPOSIUM_SSO_SALON);
        $sessionSalon = $this->extraParameterRepository->findByEventAndType($command->event, Type::TYPE_COMEXPOSIUM_SSO_SESSION_SALON);
        $application = $this->extraParameterRepository->findByEventAndType($command->event, Type::TYPE_COMEXPOSIUM_SSO_APPLICATION);

        if (null === $salon || null === $sessionSalon || null === $application) {
            return self::RESPONSE_MISSING_PARAMETERS;
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
            $response = $this->httpAdapter->post($this->comexposiumSsoCreateUserEndPoint, [], json_encode($payload));

            if (200 !== $response->statusCode) {
                return self::RESPONSE_ERROR;
            }

            /**
             * Example of response:
             * {
             *     "requestId":"802d6330-29e8-4318-9747-eefd43d0df78",
             *     "status":200,
             *     "error":null,
             *     "controller":"Comexposium/AuthController",
             *     "action":"createUser",
             *     "collection":null,
             *     "index":null,
             *     "volatile":null,
             *     "result":{
             *         "statusCode":143,
             *         "message":"create_user_already_exist"
             *     }
             * }
             *
             * result.statusCode:
             *     0     => user created
             *     143   => user already exist
             *     1-187 => errors
             */
            $body = json_decode($response->body, true);

            if (isset($body['result']['statusCode'])) {
                $statusCode = $body['result']['statusCode'];

                if (0 === (int) $statusCode) {
                    return self::RESPONSE_CREATED;
                }

                if (143 === (int) $statusCode) {
                    return self::RESPONSE_ALREADY_CREATED;
                }
            }

            return self::RESPONSE_ERROR;
        } catch (ServerErrorException $exception) {
            return self::RESPONSE_ERROR;
        }
    }
}
