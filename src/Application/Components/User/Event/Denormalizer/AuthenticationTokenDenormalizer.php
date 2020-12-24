<?php

namespace Proximum\Vimeet\Application\Components\User\Event\Denormalizer;

use Proximum\Vimeet\Application\View\User\Event\AuthenticationTokenImportView;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthenticationTokenDenormalizer implements DenormalizerInterface
{
    private const KEY_TOKEN = 'token';
    private const KEY_EMAIL = 'email';
    private const KEY_EXPIRATION = 'expiration';

    public const ALLOWED_KEYS = [
        self::KEY_EMAIL,
        self::KEY_TOKEN,
        self::KEY_EXPIRATION,
    ];

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function denormalize($data, $class, $format = null, array $context = []): iterable
    {
        if (!$context['event'] instanceof Event) {
            throw new \InvalidArgumentException();
        }

        $imports = [];

        // If there is only one line on imported file, convert it to array
        if (isset($data[self::KEY_EMAIL])) {
            $data = [$data];
        }

        foreach ($data as $key => $row) {
            $row = $this->cleanRow($row);

            try {
                if (false === $this->areKeysValid($row)) {
                    throw new InvalidKeysException('validators.authentication_token.csv.invalid_keys');
                }

                if ($row[self::KEY_EXPIRATION]) {
                    $dateValidations = $this->validator->validate($row[self::KEY_EXPIRATION], [new Date()]);

                    if ($dateValidations->count() > 0) {
                        throw new \Exception('validators.authentication_token.csv.invalid_expiration_date');
                    }
                }

                $authenticationTokenImport = new AuthenticationTokenImport(
                    new AuthenticationTokenImportView(
                        $context['event'],
                        strtolower(StringHelper::trimSpacesAndNonBreakSpaces($row[self::KEY_EMAIL])),
                        $row[self::KEY_TOKEN],
                        $row[self::KEY_EXPIRATION] ? new \DateTime($row[self::KEY_EXPIRATION]) : null
                    )
                );
            } catch (\Exception $exception) {
                $authenticationTokenImport = (new AuthenticationTokenImport())
                    ->addError($exception->getMessage());
            }

            $imports[] = $authenticationTokenImport;
        }

        return $imports;
    }

    private function cleanRow(array $row): array
    {
        return array_filter($row, function ($index) {
            return !empty($index);
        }, ARRAY_FILTER_USE_KEY);
    }

    private function areKeysValid(array &$row): bool
    {
        foreach (self::ALLOWED_KEYS as $key) {
            if (!array_key_exists($key, $row)) {
                return false;
            }
        }

        return true;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return 'csv' === $format && AuthenticationTokenImport::class === $type;
    }
}
