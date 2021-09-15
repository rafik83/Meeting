<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Count;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaResourceCountAction
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->dateTime = $dateTime;
    }

    public function __invoke(
        Event $event,
        Type $type
    ) {
        if ($event->getId() !== 253 || 1552 !== $type->getId()) {
            throw new AccessDeniedException('access denied for other event and other type.');
        }


        $array = [
            'fiche;element',
        ];
        $sheets = $this->sheetRepository->getByTypes([$type]);

        foreach ($sheets as $sheet) {
            $data = $sheet->getData();

            $linkForSheet = 0;

            // id to check
            // M2541Md657
            // M199fMfc3c

            if (isset($data['M2541Md657']) && is_array($data['M2541Md657'])) {
                foreach ($data['M2541Md657'] as $element) {
                    if (isset($element['path'])) {
                        ++$linkForSheet;
                    }
                }
            }

            if (isset($data['M199fMfc3c']['medias']) && is_array($data['M199fMfc3c']['medias'])) {
                foreach ($data['M199fMfc3c']['medias'] as $element) {
                    if (isset($element['url'])) {
                        ++$linkForSheet;
                    }
                }
            }

            $sheetId = $sheet->getId();
            $array[] = "$sheetId;$linkForSheet";
        }

        $dateFormatted = $this->dateTime->format('Y-m-d');

        return new CsvFileResponse(
            implode("\n", $array),
            "apprentissage-annonce-$dateFormatted.csv"
        );
    }
}
