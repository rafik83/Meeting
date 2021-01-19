<?php

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Sheet\FilledFilter;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;

class TemplateFilledFilter
{
    public static function getFilledFilterValues(TemplateData $templateData, Sheet $sheet): array
    {
        $filters = [];

        foreach ($templateData->getObjects() as $object) {
            if ($object instanceof UploadObject && $object->isFilter()) {
                if ($object->hasTag(Tag::SHEET_DATA)) {
                    $filters[] = [
                        'key' => $object->getKey(),
                        'status' => null === $object->getPath() ? FilledFilter::NOT_FILLED : FilledFilter::FILLED,
                        'tags' => $object->getTags(),
                    ];
                } elseif ($object->hasTag(Tag::PARTICIPANT_DATA)) {
                    $participants = $sheet->getParticipantsArray();
                    $numberOfFilledParticipant = 0;

                    /** @var Participant $participant */
                    foreach ($participants as $participant) {
                        if (isset($participant->getData()[$object->getKey()]['path'])) {
                            ++$numberOfFilledParticipant;
                        }
                    }

                    if (0 === $numberOfFilledParticipant) {
                        $status = FilledFilter::NOT_FILLED;
                    } elseif (\count($participants) === $numberOfFilledParticipant) {
                        $status = FilledFilter::FILLED;
                    } else {
                        $status = FilledFilter::PARTLY_FILLED;
                    }

                    $filters[] = [
                        'key' => $object->getKey(),
                        'status' => $status,
                        'tags' => $object->getTags(),
                    ];
                }
            }
        }

        return $filters;
    }

    public static function getFilledFilterLabel(TemplateData $templateData): array
    {
        $filters = [];

        foreach ($templateData->getObjects() as $object) {
            if ($object instanceof UploadObject && $object->isFilter()) {
                $filters[] = [
                    'key' => $object->getKey(),
                    'value' => $object->getFilterLabel(),
                    'tags' => $object->getTags(),
                ];
            }
        }

        return $filters;
    }

    public static function getFilledFilters(array $templateDatas): array
    {
        $filters = [];

        foreach ($templateDatas as $templateData) {
            $filters = array_merge($filters, self::getFilledFilterLabel($templateData));
        }

        return $filters;
    }
}
