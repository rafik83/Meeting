<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\PromotionCode\Batch;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var SerializerAdapterInterface */
    private $serializer;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        SerializerAdapterInterface $serializer
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request, Event $event, PromotionCodeGroup $promotionCodeGroup): CsvFileResponse
    {
        if (!$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $promotionCodeGroup->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        return new CsvFileResponse(
            Charset::convertString(
                $this->serializer->serialize(
                    $this->promotionCodeRepository->getPromotionCodeExportedViewByGroup($promotionCodeGroup),
                    'csv',
                    [
                        'csv_delimiter' => ';',
                    ]
                )
            ),
            'export_promotion_code_' . date('Y_m_d_His') . '.csv'
        );
    }
}
