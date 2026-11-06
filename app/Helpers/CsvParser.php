<?php

namespace App\Helpers;

class CsvParser
{
    /**
     * Parse a CSV file into an array of associative row arrays.
     *
     * @return array<int, array<string, string>>
     */
    public static function parse(string $filePath): array
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        // Remove UTF-8 BOM if present
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^{$bom}/", '', $content);

        // Normalize line breaks
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = array_filter(explode("\n", $content), fn ($l) => trim($l) !== '');

        if (empty($lines)) {
            return [];
        }

        // Determine delimiter from first line
        $firstLine = reset($lines);
        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');
        $tabCount = substr_count($firstLine, "\t");

        $delimiter = ',';
        if ($semicolonCount > $commaCount && $semicolonCount > $tabCount) {
            $delimiter = ';';
        } elseif ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            $delimiter = "\t";
        }

        $stream = fopen('php://memory', 'r+');
        if ($stream === false) {
            return [];
        }

        fwrite($stream, $content);
        rewind($stream);

        $headers = null;
        $rows = [];

        while (($data = fgetcsv($stream, 0, $delimiter)) !== false) {
            // Skip empty rows
            if (count($data) === 1 && ($data[0] === null || trim((string) $data[0]) === '')) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(function ($h) {
                    $cleaned = trim((string) $h);
                    $cleaned = strtolower($cleaned);
                    $cleaned = str_replace([' ', '-', '/'], '_', $cleaned);

                    return preg_replace('/[^a-z0-9_]/', '', $cleaned);
                }, $data);

                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if ($header !== '') {
                    $row[$header] = isset($data[$index]) ? trim((string) $data[$index]) : '';
                }
            }

            $rows[] = $row;
        }

        fclose($stream);

        return $rows;
    }
}
