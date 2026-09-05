<?php

namespace App\Services;

/**
 * Reads the CSV artifacts produced by the Python segmentation pipeline
 * (segment_profiles.csv, segmented_data.csv) and exposes them as simple
 * PHP arrays for the Blade views.
 */
class SegmentationResultReader
{
    public function __construct(public string $outputDir)
    {
        $this->outputDir = rtrim($outputDir, '/');
    }

    public function profilesPath(): string
    {
        return $this->outputDir . '/segment_profiles.csv';
    }

    public function dataPath(): string
    {
        return $this->outputDir . '/segmented_data.csv';
    }

    public function elbowImagePath(): string
    {
        return $this->outputDir . '/elbow_plot.png';
    }

    public function pcaImagePath(): string
    {
        return $this->outputDir . '/pca_clusters.png';
    }

    public function logPath(): string
    {
        return $this->outputDir . '/run.log';
    }

    /**
     * Reads a CSV into ['headers' => [...], 'rows' => [ [...], ... ]]
     */
    public function readCsv(string $path, ?int $limit = null): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        $rows = [];
        $headers = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle) ?: [];
            $count = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if ($limit !== null && $count >= $limit) {
                    break;
                }
                if (count($row) === count($headers)) {
                    $rows[] = array_combine($headers, $row);
                }
                $count++;
            }
            fclose($handle);
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    public function countDataRows(): int
    {
        $path = $this->dataPath();
        if (!file_exists($path)) {
            return 0;
        }
        $count = -1;
        $handle = fopen($path, 'r');
        while (fgets($handle) !== false) {
            $count++;
        }
        fclose($handle);
        return max(0, $count);
    }
}
