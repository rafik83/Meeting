<?php

namespace Proximum\Vimeet\Application\Nomenclature\Export;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class CsvExporter implements ExporterInterface
{
    /**
     * {@inheritdoc}
     */
    public function export(Nomenclature $nomenclature, $output, $charset)
    {
        $file    = new \SplFileObject($output, 'w+');
        $locales = [];
        $rows    = [];
        $data    = $nomenclature->getValue();

        // Append rows
        $this->append($data, 1, $rows, $locales);

        // Preprend header with locales
        array_unshift($rows, array_merge([''], array_unique($locales)));

        $max = max(array_map('count', $rows));

        foreach ($rows as $row) {
            $file->fputcsv(array_pad($row, $max, ''), ';');
        }

        $filename = Charset::convert($output, Charset::UTF_8, $charset, $output);

        return new \SplFileObject($filename);
    }

    /**
     * @param array $data
     * @param int   $depth
     * @param array $rows
     * @param array $locales
     */
    private function append($data, $depth, array &$rows, array &$locales)
    {
        foreach ($data as $id => $child) {
            if (!isset($child['label']) || !is_array($child['label'])) {
                continue;
            }

            $row = array_pad([$id], $depth, '');

            foreach ($child['label'] as $locale => $label) {
                $locales[] = $locale;
                $row[]     = $label;
            }

            $rows[] = $row;

            if (isset($child['children'])) {
                $this->append($child['children'], $depth + 1, $rows, $locales);
            }
        }
    }
}
