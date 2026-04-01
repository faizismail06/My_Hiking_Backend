<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

class GpxRouteService
{
    /**
     * @return array{points: array<int, array<string, mixed>>, original_count: int, stored_count: int}
     */
    public function parseFromPath(string $filePath, int $maxPoints = 1500): array
    {
        if (!is_file($filePath)) {
            throw new InvalidArgumentException("File GPX tidak ditemukan: {$filePath}");
        }

        $xmlContent = @file_get_contents($filePath);
        if ($xmlContent === false) {
            throw new RuntimeException('Gagal membaca file GPX.');
        }

        return $this->parseFromXml($xmlContent, $maxPoints);
    }

    /**
     * @return array{points: array<int, array<string, mixed>>, original_count: int, stored_count: int}
     */
    public function parseFromUploadedFile(UploadedFile $file, int $maxPoints = 1500): array
    {
        if (!$file->isValid()) {
            throw new InvalidArgumentException('File GPX gagal diunggah atau tidak valid.');
        }

        $xmlContent = @file_get_contents($file->getRealPath());
        if ($xmlContent === false) {
            throw new RuntimeException('Gagal membaca file GPX yang diunggah.');
        }

        return $this->parseFromXml($xmlContent, $maxPoints);
    }

    /**
     * @return array{points: array<int, array<string, mixed>>, original_count: int, stored_count: int}
     */
    private function parseFromXml(string $xmlContent, int $maxPoints): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if ($xml === false) {
            $errorMessages = [];
            foreach (libxml_get_errors() as $error) {
                $errorMessages[] = trim($error->message);
            }
            libxml_clear_errors();

            if (!empty($errorMessages)) {
                throw new RuntimeException('Format GPX tidak valid: ' . implode(' | ', $errorMessages));
            }

            throw new RuntimeException('Format GPX tidak valid.');
        }

        $trkPoints = $xml->xpath('//*[local-name()="trkpt"]') ?: [];
        if (empty($trkPoints)) {
            throw new RuntimeException('Tidak ditemukan elemen trkpt pada file GPX.');
        }

        $points = [];
        foreach ($trkPoints as $trkPoint) {
            $lat = (string) ($trkPoint['lat'] ?? '');
            $lng = (string) ($trkPoint['lon'] ?? '');

            if ($lat === '' || $lng === '') {
                continue;
            }

            $latValue = (float) $lat;
            $lngValue = (float) $lng;
            if ($latValue < -90 || $latValue > 90 || $lngValue < -180 || $lngValue > 180) {
                continue;
            }

            $eleNode = $trkPoint->xpath('./*[local-name()="ele"]');
            $timeNode = $trkPoint->xpath('./*[local-name()="time"]');

            $point = [
                'lat' => $latValue,
                'lng' => $lngValue,
            ];

            if (!empty($eleNode) && trim((string) $eleNode[0]) !== '') {
                $point['ele'] = (float) $eleNode[0];
            }

            if (!empty($timeNode) && trim((string) $timeNode[0]) !== '') {
                $point['time'] = (string) $timeNode[0];
            }

            $points[] = $point;
        }

        if (empty($points)) {
            throw new RuntimeException('Semua titik track tidak valid setelah proses sanitasi.');
        }

        $originalCount = count($points);
        $storedPoints = $this->downSamplePoints($points, max(1, $maxPoints));

        return [
            'points' => array_values($storedPoints),
            'original_count' => $originalCount,
            'stored_count' => count($storedPoints),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $points
     * @return array<int, array<string, mixed>>
     */
    private function downSamplePoints(array $points, int $maxPoints): array
    {
        $count = count($points);
        if ($count <= $maxPoints) {
            return $points;
        }

        $stride = (int) ceil($count / $maxPoints);
        $sampled = [];
        foreach ($points as $index => $point) {
            if ($index % $stride === 0) {
                $sampled[] = $point;
            }
        }

        if (end($sampled) !== end($points)) {
            $sampled[] = end($points);
        }

        return $sampled;
    }
}
