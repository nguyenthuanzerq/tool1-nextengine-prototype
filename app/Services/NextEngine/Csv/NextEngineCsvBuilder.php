<?php

namespace App\Services\NextEngine\Csv;

class NextEngineCsvBuilder
{
    public function build(array $headers, array $rows): string
    {
        $fp = fopen('php://temp', 'r+');

        // header
        fputcsv($fp, $headers);

        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $header) {
                $line[] = $row[$header] ?? '';
            }
            fputcsv($fp, $line);
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv;
    }
}
