<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Exception\Event\ExtraParameter\BadFormattedExtraParameterValueException;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class LeniBadgeLinkParametersQueryHandler
{
    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(ExtraParameterRepositoryInterface $extraParameterRepository)
    {
        $this->extraParameterRepository = $extraParameterRepository;
    }

    public function handle(LeniBadgeLinkParametersQuery $query): ?LeniBadgeLinkParametersView
    {
        $linkParameter = $this->extraParameterRepository->findByEventAndType(
            $query->event,
            Type::TYPE_LENI_BADGE_LINK
        );

        if (null === $linkParameter) {
            return null;
        }

        $serializedParameter = $linkParameter->getValue();

        $parameter = json_decode($serializedParameter, true);

        if (null === $parameter) {
            throw new BadFormattedExtraParameterValueException(
                sprintf("%s value can't be decoded", Type::TYPE_LENI_BADGE_LINK)
            );
        }

        if (!isset($parameter['concerned_type_ids'])) {
            throw new BadFormattedExtraParameterValueException('concerned_type_ids key is missing');
        }

        if (!is_array($parameter['concerned_type_ids'])) {
            throw new BadFormattedExtraParameterValueException('concerned_type_ids key has to be an array');
        }

        if (!isset($parameter['link'])) {
            throw new BadFormattedExtraParameterValueException('link key is missing');
        }

        if (!is_string($parameter['link'])) {
            throw new BadFormattedExtraParameterValueException('link key has to be a string');
        }

        return new LeniBadgeLinkParametersView($parameter['link'], $parameter['concerned_type_ids']);
    }
}
