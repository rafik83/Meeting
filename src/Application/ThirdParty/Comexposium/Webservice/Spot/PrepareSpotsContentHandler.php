<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Serializer\Charset;

class PrepareSpotsContentHandler
{
    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    public function __construct(SerializerAdapterInterface $serializerAdapter)
    {
        $this->serializerAdapter = $serializerAdapter;
    }

    public function handle(PrepareSpotsContent $prepareSpotsContent)
    {
        $spots = [];

        foreach ($prepareSpotsContent->rawRegistrationDataIndexedBySheetId as $sheetId => $rawData) {
            if (!isset($rawData->stand)) {
                continue;
            }

            $rawSpots = $this->convertToArray($rawData->stand);

            foreach ($rawSpots as $rawSpot) {
                $spots[] = array_merge(
                    ['sheet_id' => $sheetId],
                    (array) $rawSpot
                );
            }
        }

        return $this->serializerAdapter->serialize($spots, 'csv', [
            'charset' => Charset::WINDOWS_1252,
            'csv_delimiter' => ';',
        ]);
    }

    protected function convertToArray($data): array
    {
        return \is_array($data) ? $data : [$data];
    }
}
