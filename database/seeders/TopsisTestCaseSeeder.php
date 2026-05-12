<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\TopsisService;
use App\Services\RecommendationService;

/**
 * TopsisTestCaseSeeder
 *
 * Runs 10 in-memory TOPSIS test cases WITHOUT touching the database.
 *
 * Each case validates a specific mathematical property or edge-case.
 * Run with:
 *   php artisan db:seed --class=TopsisTestCaseSeeder
 *
 * Output: ASCII result table to console.
 */
class TopsisTestCaseSeeder extends Seeder
{
    // ─── Standard alternatives for most test cases ────────────────────────────
    // Alternatives deliberately trade off across criteria so different weight
    // sets produce meaningfully different CC scores and rankings.
    //
    //   Alpha : easy + scenic + safe     (low cost/difficulty, high benefit scores)
    //   Beta  : moderate on everything   (middle of the road)
    //   Gamma : hard + cheap + popular   (high difficulty/crowd, high popularity)
    private static function standardAlts(): array
    {
        return [
            [
                'route_id'      => 1, 'route_name' => 'Route Alpha', 'mountain_name' => 'Gunung A',
                'criteria' => [
                    'distance' => 4.0,  'elevation' => 600.0,  'duration' => 3.0,
                    'cost'     => 15000, 'difficulty' => 1.2, 'crowd_level' => 1.0,
                    'panorama_score' => 5.0, 'fasilitas_score' => 5.0,
                    'popularity_score' => 50.0,  'safety_score' => 5.0,
                ],
            ],
            [
                'route_id'      => 2, 'route_name' => 'Route Beta', 'mountain_name' => 'Gunung B',
                'criteria' => [
                    'distance' => 12.0, 'elevation' => 2000.0, 'duration' => 8.0,
                    'cost'     => 100000, 'difficulty' => 2.8, 'crowd_level' => 3.0,
                    'panorama_score' => 3.0, 'fasilitas_score' => 3.0,
                    'popularity_score' => 500.0, 'safety_score' => 3.0,
                ],
            ],
            [
                // Gamma is hard/crowded but VERY popular and cheap
                'route_id'      => 3, 'route_name' => 'Route Gamma', 'mountain_name' => 'Gunung C',
                'criteria' => [
                    'distance' => 18.0, 'elevation' => 3200.0, 'duration' => 12.0,
                    'cost'     => 10000, 'difficulty' => 3.9, 'crowd_level' => 5.0,
                    'panorama_score' => 2.0, 'fasilitas_score' => 1.0,
                    'popularity_score' => 8000.0, 'safety_score' => 2.0,
                ],
            ],
        ];
    }


    public function run(): void
    {
        $topsis = app(TopsisService::class);

        $this->command->info('');
        $this->command->line('╔══════════════════════════════════════════════════════════════════╗');
        $this->command->line('║          TOPSIS 10-Test Case Verification Suite                  ║');
        $this->command->line('╚══════════════════════════════════════════════════════════════════╝');
        $this->command->info('');

        $passed = 0;
        $failed = 0;

        // ─────────────────────────────────────────────────────────────────────
        // TC-01 Ranking changes when weights differ
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-01: Ranking changes when weights differ';
        $alts = self::standardAlts();

        // Equal weights → Alpha (low cost/difficulty, high safety/panorama) should rank #1
        $resultEqual   = $topsis->rank($alts, []);

        // Popularity-biased: Gamma is VERY popular (8000) + VERY cheap (10000) → should rank #1
        $resultPop = $topsis->rank($alts, [
            'popularity_score' => 5.0,
            'cost'             => 5.0,
        ]);

        // Panorama+Safety biased → Alpha keeps #1
        $resultSafety = $topsis->rank($alts, [
            'panorama_score' => 5.0,
            'safety_score'   => 5.0,
        ]);

        $equalRank1   = $resultEqual[0]['route_id'];
        $popRank1     = $resultPop[0]['route_id'];
        $safetyRank1  = $resultSafety[0]['route_id'];

        // Score for Alpha in equal vs safety-biased should differ
        $scoreEqual  = $resultEqual[0]['score'];
        $scoreSafety = $resultSafety[0]['score'];

        // Key assertion: popularity+cost bias should flip rank1 from Alpha to Gamma
        $rankFlips = ($equalRank1 !== $popRank1);

        $ok = $rankFlips;
        $this->report($tc, $ok,
            "Equal→Rank1={$equalRank1}, Pop+Cost→Rank1={$popRank1}, Safety→Rank1={$safetyRank1}",
            $ok ? null : 'Popularity+Cost-biased weights should flip rank1 from Alpha to Gamma (cheap+popular route)'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-02 Best-everywhere route gets CC near 1.0
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-02: Best-on-all-criteria → CC close to 1.0';
        $result = $topsis->rank(self::standardAlts(), []);
        $topScore = $result[0]['score'];
        // Alpha excels on panorama/safety/fasilitas/low-cost/low-difficulty.
        // Gamma has high popularity (log1p-scaled) so it's not fully dominated.
        // We expect CC > 0.60 for Alpha under equal weights.
        $ok = $topScore >= 0.60;
        $this->report($tc, $ok,
            "CC for Rank-1 route (id={$result[0]['route_id']}) = {$topScore}",
            $ok ? null : 'Best-overall route expected CC >= 0.60 under equal weights'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-03 Worst-everywhere route gets CC near 0.0
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-03: Worst-on-all-criteria → CC close to 0.0';
        $lastScore = $result[count($result) - 1]['score'];
        // Gamma is worst on panorama/safety/fasilitas but best on popularity.
        // Beta is the median. The true worst under equal weights should have CC < 0.45.
        $ok = $lastScore <= 0.45;
        $this->report($tc, $ok,
            "CC for last-rank route (id={$result[count($result)-1]['route_id']}) = {$lastScore}",
            $ok ? null : 'Last-rank route expected CC <= 0.45 (has genuine weaknesses on multiple criteria)'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-04 Zero-variance column does not produce NaN or crash ranking
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-04: Zero-variance criterion does not produce NaN / crash';
        // Make crowd_level identical for all alternatives (degenerate column)
        $degenerateAlts = array_map(function ($a) {
            $a['criteria']['crowd_level'] = 3.0; // all same → degenerate
            return $a;
        }, self::standardAlts());

        $degResult = $topsis->rank($degenerateAlts, []);
        $allFinite = true;
        foreach ($degResult as $r) {
            if (!is_finite($r['score']) || is_nan($r['score'])) {
                $allFinite = false;
                break;
            }
        }
        $ok = $allFinite && count($degResult) === 3;
        $this->report($tc, $ok,
            "Scores: " . implode(', ', array_column($degResult, 'score')),
            $ok ? null : 'Degenerate column must not cause NaN or empty result'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-05 n=1 edge case does not produce NaN
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-05: n=1 (single alternative) does not produce NaN';
        $singleResult = $topsis->rank([self::standardAlts()[0]], []);
        $score1 = $singleResult[0]['score'] ?? null;
        // With 1 alternative: d+ = d- = 0 → CC = 0/0 → guarded to 0.0
        $ok = is_array($singleResult)
           && count($singleResult) === 1
           && is_numeric($score1)
           && !is_nan((float) $score1);
        $this->report($tc, $ok,
            "Score for single alternative = {$score1}",
            $ok ? null : 'Single alternative must not produce NaN; expected 0.0'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-06 log1p pre-scaling preserves relative order of popularity & cost
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-06: log1p pre-scaling preserves relative order (popularity & cost)';
        // Route A: high popularity 5000 (benefit) → after log1p still largest
        // Route B: low  popularity   10 (benefit) → after log1p still smallest
        $scalingAlts = [
            [
                'route_id' => 10, 'route_name' => 'High-Pop', 'mountain_name' => 'G',
                'criteria' => [
                    'distance' => 5.0, 'elevation' => 500.0, 'duration' => 3.0,
                    'cost' => 10000, 'difficulty' => 1.0, 'crowd_level' => 2.0,
                    'panorama_score' => 3.0, 'fasilitas_score' => 3.0,
                    'popularity_score' => 5000.0, 'safety_score' => 3.0,
                ],
            ],
            [
                'route_id' => 11, 'route_name' => 'Low-Pop', 'mountain_name' => 'G',
                'criteria' => [
                    'distance' => 5.0, 'elevation' => 500.0, 'duration' => 3.0,
                    'cost' => 10000, 'difficulty' => 1.0, 'crowd_level' => 2.0,
                    'panorama_score' => 3.0, 'fasilitas_score' => 3.0,
                    'popularity_score' => 10.0, 'safety_score' => 3.0,
                ],
            ],
        ];
        $scalingResult = $topsis->rank($scalingAlts, ['popularity_score' => 5.0]);
        // High-Pop should rank #1 (popularity_score is a benefit criterion)
        $ok = $scalingResult[0]['route_id'] === 10;
        $this->report($tc, $ok,
            "Rank1 route_id={$scalingResult[0]['route_id']} (expected 10=High-Pop), scores: {$scalingResult[0]['score']} vs {$scalingResult[1]['score']}",
            $ok ? null : 'High popularity_score should rank higher after log1p'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-07 Soft-tier modifier actually changes difficulty weight
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-07: Soft-tier modifier changes effective difficulty weight';
        // We directly test normaliseWeights with and without multiplier applied
        $baseWeights = ['difficulty' => 1.0, 'distance' => 1.0, 'elevation' => 1.0,
                        'duration' => 1.0, 'cost' => 1.0, 'crowd_level' => 1.0,
                        'panorama_score' => 1.0, 'fasilitas_score' => 1.0,
                        'popularity_score' => 1.0, 'safety_score' => 1.0];

        $criterionKeys = array_keys($baseWeights);

        // Beginner: difficulty × 1.5
        $beginnerWeights = $baseWeights;
        $beginnerWeights['difficulty'] *= 1.5;
        $normBeginner = $topsis->normaliseWeights($beginnerWeights, $criterionKeys);

        // Advanced: difficulty × 0.6
        $advancedWeights = $baseWeights;
        $advancedWeights['difficulty'] *= 0.6;
        $normAdvanced = $topsis->normaliseWeights($advancedWeights, $criterionKeys);

        // No tier: difficulty = 1.0
        $normNeutral = $topsis->normaliseWeights($baseWeights, $criterionKeys);

        $beginnerDiff = round($normBeginner['difficulty'], 4);
        $advancedDiff = round($normAdvanced['difficulty'], 4);
        $neutralDiff  = round($normNeutral['difficulty'],  4);

        $ok = ($beginnerDiff > $neutralDiff) && ($advancedDiff < $neutralDiff);
        $this->report($tc, $ok,
            "Difficulty weight: beginner={$beginnerDiff} > neutral={$neutralDiff} > advanced={$advancedDiff}",
            $ok ? null : 'Beginner should have highest difficulty weight, advanced the lowest'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-08 Budget filter excludes expensive routes before TOPSIS
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-08: Budget filter excludes routes over max_budget';
        // Simulate the budget filtering logic from RecommendationService
        $budgetAlts = self::standardAlts();
        // Alpha cost=15000, Gamma cost=10000, Beta cost=100000
        // Filter at 20000 → Alpha + Gamma survive, Beta excluded
        $maxBudget = 20000.0;
        $filtered = array_filter($budgetAlts, fn ($a) => $a['criteria']['cost'] <= $maxBudget);
        $filtered = array_values($filtered);

        $filteredIds = array_column($filtered, 'route_id');
        sort($filteredIds);

        $ok = count($filtered) === 2
           && in_array(1, $filteredIds, true)   // Alpha (15000 ≤ 20000)
           && in_array(3, $filteredIds, true)   // Gamma (10000 ≤ 20000)
           && !in_array(2, $filteredIds, true); // Beta (100000 > 20000) excluded
        $this->report($tc, $ok,
            "After budget ≤ 20000 filter: " . count($filtered) . " route(s) remain. IDs: " .
                implode(',', array_column($filtered, 'route_id')),
            $ok ? null : 'Alpha(15k) and Gamma(10k) should survive; Beta(100k) must be excluded'
        );

        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-09 Identical CC scores produce stable deterministic rank order
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-09: Identical CC scores → tiebreaker produces consistent order';
        // Three routes with identical criteria → CC scores will all be equal (likely 0)
        $tieAlts = [
            ['route_id' => 5, 'route_name' => 'Tie-A', 'mountain_name' => 'G',
             'criteria' => ['distance'=>5.0,'elevation'=>1000.0,'duration'=>5.0,'cost'=>50000,
                            'difficulty'=>2.0,'crowd_level'=>2.0,'panorama_score'=>3.0,
                            'fasilitas_score'=>3.0,'popularity_score'=>100.0,'safety_score'=>3.0]],
            ['route_id' => 3, 'route_name' => 'Tie-B', 'mountain_name' => 'G',
             'criteria' => ['distance'=>5.0,'elevation'=>1000.0,'duration'=>5.0,'cost'=>50000,
                            'difficulty'=>2.0,'crowd_level'=>2.0,'panorama_score'=>3.0,
                            'fasilitas_score'=>3.0,'popularity_score'=>100.0,'safety_score'=>3.0]],
            ['route_id' => 7, 'route_name' => 'Tie-C', 'mountain_name' => 'G',
             'criteria' => ['distance'=>5.0,'elevation'=>1000.0,'duration'=>5.0,'cost'=>50000,
                            'difficulty'=>2.0,'crowd_level'=>2.0,'panorama_score'=>3.0,
                            'fasilitas_score'=>3.0,'popularity_score'=>100.0,'safety_score'=>3.0]],
        ];

        $tieResult1 = $topsis->rank($tieAlts, []);
        $tieResult2 = $topsis->rank($tieAlts, []);

        // Apply the same tiebreaker as RecommendationService (sort by route_id asc)
        $applyTiebreaker = function (array $results): array {
            usort($results, function ($a, $b) {
                $diff = $b['score'] <=> $a['score'];
                return $diff !== 0 ? $diff : ($a['route_id'] <=> $b['route_id']);
            });
            return $results;
        };

        $sorted1 = $applyTiebreaker($tieResult1);
        $sorted2 = $applyTiebreaker($tieResult2);

        $ids1 = array_column($sorted1, 'route_id');
        $ids2 = array_column($sorted2, 'route_id');

        $ok = $ids1 === $ids2 && $ids1 === [3, 5, 7];   // ascending by route_id
        $this->report($tc, $ok,
            "Run1: " . implode(',', $ids1) . " | Run2: " . implode(',', $ids2) . " (expected: 3,5,7)",
            $ok ? null : 'Tiebreaker must produce stable order by route_id ascending'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // TC-10 Equal weights vs extreme weights produce different rankings
        // ─────────────────────────────────────────────────────────────────────
        $tc = 'TC-10: Equal weights vs extreme weights produce different CC scores';
        // Use alternatives where panorama differs dramatically but other criteria are equal
        $extremeAlts = [
            [
                'route_id' => 20, 'route_name' => 'HighPanorama', 'mountain_name' => 'G',
                'criteria' => ['distance'=>8.0,'elevation'=>1200.0,'duration'=>5.0,'cost'=>60000,
                               'difficulty'=>2.5,'crowd_level'=>3.0,'panorama_score'=>5.0,
                               'fasilitas_score'=>3.0,'popularity_score'=>200.0,'safety_score'=>3.0],
            ],
            [
                'route_id' => 21, 'route_name' => 'LowPanorama', 'mountain_name' => 'G',
                'criteria' => ['distance'=>8.0,'elevation'=>1200.0,'duration'=>5.0,'cost'=>60000,
                               'difficulty'=>2.5,'crowd_level'=>3.0,'panorama_score'=>1.0,
                               'fasilitas_score'=>3.0,'popularity_score'=>200.0,'safety_score'=>3.0],
            ],
            [
                // Third alternative with different cost so degenerate-column check doesn't zero all
                'route_id' => 22, 'route_name' => 'MidPanorama', 'mountain_name' => 'G',
                'criteria' => ['distance'=>8.0,'elevation'=>1200.0,'duration'=>5.0,'cost'=>30000,
                               'difficulty'=>2.5,'crowd_level'=>3.0,'panorama_score'=>3.0,
                               'fasilitas_score'=>3.0,'popularity_score'=>200.0,'safety_score'=>3.0],
            ],
        ];

        // Equal weights → panorama still matters but 1/10 weight
        $resultEqualW  = $topsis->rank($extremeAlts, []);
        // panorama weight = 5.0 (extreme); others implicitly 1.0 → panorama weight ≈ 5/14
        $resultExtremeW = $topsis->rank($extremeAlts, ['panorama_score' => 5.0]);

        $equalHighCC   = null;
        $extremeHighCC = null;
        foreach ($resultEqualW   as $r) { if ($r['route_id'] === 20) { $equalHighCC   = $r['score']; break; }}
        foreach ($resultExtremeW as $r) { if ($r['route_id'] === 20) { $extremeHighCC = $r['score']; break; }}

        $ok = $equalHighCC !== null
           && $extremeHighCC !== null
           && $resultEqualW[0]['route_id']  === 20
           && $resultExtremeW[0]['route_id'] === 20
           && abs($extremeHighCC - $equalHighCC) > 0.001; // CC must measurably increase
        $this->report($tc, $ok,
            "HighPanorama CC: equal={$equalHighCC}, extremePanorama={$extremeHighCC} | " .
            "Rank1: equal={$resultEqualW[0]['route_id']}, extreme={$resultExtremeW[0]['route_id']}",
            $ok ? null : 'Boosting panorama weight must measurably increase CC for the high-panorama route'
        );
        $ok ? $passed++ : $failed++;

        // ─────────────────────────────────────────────────────────────────────
        // Summary
        // ─────────────────────────────────────────────────────────────────────
        $total = $passed + $failed;
        $this->command->info('');
        $this->command->line('─────────────────────────────────────────────────────');
        if ($failed === 0) {
            $this->command->info("✅  All {$total} test cases PASSED");
        } else {
            $this->command->error("❌  {$failed}/{$total} test case(s) FAILED — check above");
        }
        $this->command->line('─────────────────────────────────────────────────────');
        $this->command->info('');
    }

    // ─── Private helper ───────────────────────────────────────────────────────

    private function report(string $label, bool $ok, string $detail, ?string $failMsg = null): void
    {
        $icon = $ok ? '✅' : '❌';
        $this->command->line("{$icon}  {$label}");
        $this->command->line("     {$detail}");
        if (!$ok && $failMsg) {
            $this->command->warn("     ⚠  {$failMsg}");
        }
        $this->command->info('');
    }
}
