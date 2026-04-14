<?php

namespace App\Services;

class TopsisService
{
    /**
     * Kriteria TOPSIS (urutannya dipakai konsisten untuk semua langkah).
     * type = cost  -> lebih kecil lebih baik
     * type = benefit -> lebih besar lebih baik
     */
    private array $criteria = [
        'distance' => ['type' => 'cost', 'weight' => 0.20],
        'elevation' => ['type' => 'cost', 'weight' => 0.20],
        'duration' => ['type' => 'cost', 'weight' => 0.15],
        'difficulty' => ['type' => 'cost', 'weight' => 0.20],
        'cost' => ['type' => 'cost', 'weight' => 0.10],
        'weather' => ['type' => 'benefit', 'weight' => 0.15],
    ];

    /**
     * @param array<int, array<string, mixed>> $alternatives
     * Bentuk setiap alternatif:
     * [
     *   'route_id' => int,
     *   'route_name' => string,
     *   'mountain_name' => string,
     *   'criteria' => [
     *      'distance' => float,
     *      'elevation' => float,
     *      'duration' => float,
     *      'difficulty' => float,
     *      'cost' => float,
     *      'weather' => float,
     *   ]
     * ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function rank(array $alternatives): array
    {
        if (empty($alternatives)) {
            return [];
        }

        $criterionKeys = array_keys($this->criteria);

        // 1) Build Decision Matrix (X)
        $decisionMatrix = [];
        foreach ($alternatives as $alternative) {
            $row = [];
            foreach ($criterionKeys as $criterionKey) {
                $row[$criterionKey] = (float) ($alternative['criteria'][$criterionKey] ?? 0.0);
            }
            $decisionMatrix[] = $row;
        }

        // 2) Normalize Matrix (R) dengan vector normalization
        // r_ij = x_ij / sqrt(sum(x_ij^2))
        $divisors = [];
        foreach ($criterionKeys as $criterionKey) {
            $sumSquares = 0.0;
            foreach ($decisionMatrix as $row) {
                $sumSquares += pow((float) $row[$criterionKey], 2);
            }
            $divisors[$criterionKey] = sqrt($sumSquares);
        }

        $normalizedMatrix = [];
        foreach ($decisionMatrix as $row) {
            $normalizedRow = [];
            foreach ($criterionKeys as $criterionKey) {
                $divisor = (float) ($divisors[$criterionKey] ?? 0.0);
                $normalizedRow[$criterionKey] = $divisor > 0.0
                    ? ((float) $row[$criterionKey] / $divisor)
                    : 0.0;
            }
            $normalizedMatrix[] = $normalizedRow;
        }

        // 3) Apply Weights (V)
        // v_ij = w_j * r_ij
        $weightedMatrix = [];
        foreach ($normalizedMatrix as $normalizedRow) {
            $weightedRow = [];
            foreach ($criterionKeys as $criterionKey) {
                $weight = (float) ($this->criteria[$criterionKey]['weight'] ?? 0.0);
                $weightedRow[$criterionKey] = $normalizedRow[$criterionKey] * $weight;
            }
            $weightedMatrix[] = $weightedRow;
        }

        // 4) Determine Positive Ideal Solution (A+) dan
        // 5) Determine Negative Ideal Solution (A-)
        $idealPositive = [];
        $idealNegative = [];

        foreach ($criterionKeys as $criterionKey) {
            $columnValues = array_column($weightedMatrix, $criterionKey);
            $isBenefit = ($this->criteria[$criterionKey]['type'] ?? 'cost') === 'benefit';

            if ($isBenefit) {
                $idealPositive[$criterionKey] = max($columnValues); // Benefit: maksimum
                $idealNegative[$criterionKey] = min($columnValues); // Benefit: minimum
            } else {
                $idealPositive[$criterionKey] = min($columnValues); // Cost: minimum
                $idealNegative[$criterionKey] = max($columnValues); // Cost: maksimum
            }
        }

        // 6) Calculate Euclidean distance ke A+ (D+) dan A- (D-)
        $distances = [];
        foreach ($weightedMatrix as $index => $weightedRow) {
            $sumPlus = 0.0;
            $sumMinus = 0.0;

            foreach ($criterionKeys as $criterionKey) {
                $sumPlus += pow($weightedRow[$criterionKey] - $idealPositive[$criterionKey], 2);
                $sumMinus += pow($weightedRow[$criterionKey] - $idealNegative[$criterionKey], 2);
            }

            $distances[$index] = [
                'd_plus' => sqrt($sumPlus),
                'd_minus' => sqrt($sumMinus),
            ];
        }

        // 7) Calculate Closeness Coefficient (CC)
        // CC_i = D-_i / (D+_i + D-_i)
        $scoredAlternatives = [];
        foreach ($alternatives as $index => $alternative) {
            $dPlus = (float) ($distances[$index]['d_plus'] ?? 0.0);
            $dMinus = (float) ($distances[$index]['d_minus'] ?? 0.0);
            $denominator = $dPlus + $dMinus;

            $cc = $denominator > 0.0 ? ($dMinus / $denominator) : 0.0;

            $scoredAlternatives[] = [
                'route_id' => (int) $alternative['route_id'],
                'route_name' => (string) $alternative['route_name'],
                'mountain_name' => (string) $alternative['mountain_name'],
                'score' => round($cc, 4),
            ];
        }

        // 8) Rank alternatives (semakin besar CC semakin baik)
        usort($scoredAlternatives, function (array $a, array $b) {
            return $b['score'] <=> $a['score'];
        });

        return $scoredAlternatives;
    }
}
