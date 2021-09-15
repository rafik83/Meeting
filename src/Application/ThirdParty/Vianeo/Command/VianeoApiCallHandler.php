<?php

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Command;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Exception\VianeoApiServerException;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Exception\VianeoSheetAlreadyRegisteredException;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Exception\VianeoSheetNotRegisteredException;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet\VianeoExtraDataType;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface;

class VianeoApiCallHandler
{
    /**
     * Example:
     * https://techinnov.vianeo.io/index.php?option=com_vianeo_select_concours&task=techinnov.createaccount&sharedsecret=xxxxx
     */
    const URL_TEMPLATE = '%endpoint%&jsonpayload=%jsonpayload%&lang=%lang%';
    const DEFAULT_LOCALE = 'fr';

    const VIANEO_RETURN_CODE = 'returnCode';
    const VIANEO_ERROR_MESSAGE = 'errorMessage';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var VianeoGetSheetDataHandler */
    private $vianeoGetSheetDataHandler;

    /** @var VianeoRegisterSheetHandler */
    private $vianeoRegisterSheetHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param HttpAdapterInterface              $httpAdapter
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param VianeoGetSheetDataHandler         $vianeoGetSheetDataHandler
     * @param VianeoRegisterSheetHandler        $vianeoRegisterSheetHandler
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     */
    public function __construct(
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        VianeoGetSheetDataHandler $vianeoGetSheetDataHandler,
        VianeoRegisterSheetHandler $vianeoRegisterSheetHandler,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->vianeoGetSheetDataHandler = $vianeoGetSheetDataHandler;
        $this->vianeoRegisterSheetHandler = $vianeoRegisterSheetHandler;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * @param VianeoApiCall $vianeoApiCall
     *
     * @throws VianeoApiServerException
     * @throws VianeoSheetNotRegisteredException
     * @throws VianeoSheetAlreadyRegisteredException
     */
    public function handle(VianeoApiCall $vianeoApiCall)
    {
        $sheet = $vianeoApiCall->sheet;
        $event = $sheet->getEvent();
        $locale = $event->getAvailableLocale(self::DEFAULT_LOCALE);

        $vianeoEndpointParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_VIANEO_ENDPOINT
        );

        $vianeoConcernedTypesIdParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_VIANEO_CONCERNED_TYPES_ID
        );

        if (null === $vianeoEndpointParameter || null === $vianeoConcernedTypesIdParameter) {
            throw new \LogicException(
                'Can not call VianeoApiCallHandler::handle() if event has not VIANEO_ENDPOINT or VIANEO_CONCERNED_TYPES_ID'
            );
        }

        if (true === $this->extraDataRepository->hasExtraDataForSheet(
                $sheet,
                VianeoExtraDataType::VIANEO_SHEET_REGISTERED
            )
        ) {
            throw new VianeoSheetAlreadyRegisteredException();
        }

        $jsonPayload = $this->vianeoGetSheetDataHandler->handle(new VianeoGetSheetData($sheet, $locale));

        $urlWithData = strtr(
            self::URL_TEMPLATE,
            [
                '%endpoint%' => $vianeoEndpointParameter->getValue(),
                '%jsonpayload%' => urlencode($jsonPayload),
                '%lang%' => self::DEFAULT_LOCALE,
            ]
        );

        try {
            $jsonResponse = $this->httpAdapter->get($urlWithData);
            $response = json_decode($jsonResponse->body, true);

            if (!array_key_exists(self::VIANEO_RETURN_CODE, $response)) {
                throw new VianeoApiServerException('Missing Vianeo return code');
            }

            $returnCode = (int) $response[self::VIANEO_RETURN_CODE];

            if (0 === $returnCode) {
                $this->vianeoRegisterSheetHandler->handle(new VianeoRegisterSheet($sheet));

                return;
            }

            if ($returnCode === -100) {
                throw new VianeoApiServerException(
                    sprintf(
                        'User already exists with this email %s',
                        $vianeoApiCall->sheet->getFirstParticipant()->getEmail()
                    )
                );
            } else {
                if ($returnCode === -403) {
                    throw new VianeoApiServerException('Access denied to VIANEO. Check VIANEO_SHARED_SECRET');
                }
            }

            throw new VianeoApiServerException(
                $response[self::VIANEO_ERROR_MESSAGE] ?? 'Server error with no message'
            );
        } catch (ServerErrorException $exception) {
            throw new VianeoApiServerException($exception);
        }
    }
}
