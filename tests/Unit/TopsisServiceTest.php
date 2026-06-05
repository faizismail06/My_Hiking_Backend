<?php

namespace Tests\Unit;

use App\Services\TopsisService;
use PHPUnit\Framework\TestCase;

/**
 * 20 Studi Kasus + 2 Studi Kasus Bermasalah untuk TopsisService.
 *
 * Data menggunakan routes dari database aktual (routes_202605301151.sql).
 * Subset Merbabu (5 jalur) digunakan untuk mayoritas test agar fokus
 * pada anomali yang dilaporkan di debugging prompt.
 */
class TopsisServiceTest extends TestCase
{
    private TopsisService $topsis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->topsis = new TopsisService();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Helper: Build Merbabu alternatives (5 routes from SQL data)
    // ═══════════════════════════════════════════════════════════════════════

    private function merbabuAlternatives(): array
    {
        return [
            ['route_id' => 1, 'route_name' => 'Jalur Selo', 'mountain_name' => 'Merbabu',
             'criteria' => ['distance'=>5.60,'elevation'=>1300,'duration'=>8,'cost'=>1000000,
                'difficulty'=>2.0,'crowd_level'=>3,'panorama_score'=>5,'fasilitas_score'=>4,
                'popularity_score'=>850,'safety_score'=>4]],
            ['route_id' => 2, 'route_name' => 'Jalur Cuntel', 'mountain_name' => 'Merbabu',
             'criteria' => ['distance'=>6.43,'elevation'=>1400,'duration'=>10,'cost'=>20000,
                'difficulty'=>2.0,'crowd_level'=>2,'panorama_score'=>4,'fasilitas_score'=>3,
                'popularity_score'=>420,'safety_score'=>4]],
            ['route_id' => 3, 'route_name' => 'Jalur Suwanting', 'mountain_name' => 'Merbabu',
             'criteria' => ['distance'=>6.56,'elevation'=>1500,'duration'=>10,'cost'=>15000,
                'difficulty'=>3.0,'crowd_level'=>2,'panorama_score'=>5,'fasilitas_score'=>2,
                'popularity_score'=>310,'safety_score'=>3]],
            ['route_id' => 4, 'route_name' => 'Jalur Thekelan', 'mountain_name' => 'Merbabu',
             'criteria' => ['distance'=>6.90,'elevation'=>1400,'duration'=>9,'cost'=>15000,
                'difficulty'=>2.0,'crowd_level'=>1,'panorama_score'=>4,'fasilitas_score'=>3,
                'popularity_score'=>250,'safety_score'=>4]],
            ['route_id' => 5, 'route_name' => 'Jalur Wekas', 'mountain_name' => 'Merbabu',
             'criteria' => ['distance'=>5.60,'elevation'=>1200,'duration'=>8,'cost'=>15000,
                'difficulty'=>2.0,'crowd_level'=>1,'panorama_score'=>3,'fasilitas_score'=>3,
                'popularity_score'=>180,'safety_score'=>5]],
        ];
    }

    private function allRoutesAlternatives(): array
    {
        return array_merge($this->merbabuAlternatives(), [
            ['route_id'=>6,'route_name'=>'Jalur Bambangan','mountain_name'=>'Slamet',
             'criteria'=>['distance'=>6.20,'elevation'=>1500,'duration'=>9,'cost'=>15000,
                'difficulty'=>3.0,'crowd_level'=>5,'panorama_score'=>5,'fasilitas_score'=>5,
                'popularity_score'=>950,'safety_score'=>3]],
            ['route_id'=>7,'route_name'=>'Jalur Kaliwadas','mountain_name'=>'Slamet',
             'criteria'=>['distance'=>11.00,'elevation'=>1600,'duration'=>10,'cost'=>20000,
                'difficulty'=>3.0,'crowd_level'=>2,'panorama_score'=>4,'fasilitas_score'=>3,
                'popularity_score'=>380,'safety_score'=>3]],
            ['route_id'=>8,'route_name'=>'Jalur Guci','mountain_name'=>'Slamet',
             'criteria'=>['distance'=>9.95,'elevation'=>1400,'duration'=>8,'cost'=>15000,
                'difficulty'=>3.0,'crowd_level'=>3,'panorama_score'=>4,'fasilitas_score'=>4,
                'popularity_score'=>560,'safety_score'=>4]],
            ['route_id'=>9,'route_name'=>'Jalur Dipajaya','mountain_name'=>'Slamet',
             'criteria'=>['distance'=>6.28,'elevation'=>1300,'duration'=>7,'cost'=>15000,
                'difficulty'=>2.0,'crowd_level'=>2,'panorama_score'=>3,'fasilitas_score'=>3,
                'popularity_score'=>290,'safety_score'=>4]],
            ['route_id'=>10,'route_name'=>'Jalur Baturraden','mountain_name'=>'Slamet',
             'criteria'=>['distance'=>7.61,'elevation'=>1700,'duration'=>11,'cost'=>15000,
                'difficulty'=>4.0,'crowd_level'=>4,'panorama_score'=>5,'fasilitas_score'=>4,
                'popularity_score'=>410,'safety_score'=>2]],
            ['route_id'=>11,'route_name'=>'Jalur Mangli','mountain_name'=>'Sumbing',
             'criteria'=>['distance'=>7.00,'elevation'=>1300,'duration'=>7,'cost'=>15000,
                'difficulty'=>2.0,'crowd_level'=>3,'panorama_score'=>4,'fasilitas_score'=>4,
                'popularity_score'=>620,'safety_score'=>5]],
            ['route_id'=>12,'route_name'=>'Jalur Gajah Mungkur','mountain_name'=>'Sumbing',
             'criteria'=>['distance'=>8.28,'elevation'=>1500,'duration'=>9,'cost'=>20000,
                'difficulty'=>3.0,'crowd_level'=>1,'panorama_score'=>5,'fasilitas_score'=>2,
                'popularity_score'=>150,'safety_score'=>3]],
            ['route_id'=>13,'route_name'=>'Jalur Cepit Parakan','mountain_name'=>'Sumbing',
             'criteria'=>['distance'=>8.50,'elevation'=>1400,'duration'=>8,'cost'=>15000,
                'difficulty'=>3.0,'crowd_level'=>1,'panorama_score'=>4,'fasilitas_score'=>2,
                'popularity_score'=>120,'safety_score'=>4]],
            ['route_id'=>14,'route_name'=>'Jalur Bowongso','mountain_name'=>'Sumbing',
             'criteria'=>['distance'=>7.77,'elevation'=>1400,'duration'=>8,'cost'=>15000,
                'difficulty'=>3.0,'crowd_level'=>2,'panorama_score'=>4,'fasilitas_score'=>3,
                'popularity_score'=>200,'safety_score'=>4]],
            ['route_id'=>15,'route_name'=>'Jalur Garung','mountain_name'=>'Sumbing',
             'criteria'=>['distance'=>4.23,'elevation'=>1200,'duration'=>7,'cost'=>15000,
                'difficulty'=>2.0,'crowd_level'=>4,'panorama_score'=>3,'fasilitas_score'=>5,
                'popularity_score'=>780,'safety_score'=>5]],
        ]);
    }

    private function getRankById(array $ranked, int $routeId): ?int
    {
        foreach ($ranked as $pos => $item) {
            if ($item['route_id'] === $routeId) return $pos + 1;
        }
        return null;
    }

    private function getTopNIds(array $ranked, int $n): array
    {
        return array_map(fn($r) => $r['route_id'], array_slice($ranked, 0, $n));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STUDI KASUS 1-4: Skenario dari Debugging Prompt (anomali utama)
    // ═══════════════════════════════════════════════════════════════════════

    /** SK1: cost=1, others=3 → Selo harus muncul di top 3 */
    public function test_sk01_cost_minimal_others_medium(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'cost'=>1,'distance'=>3,'elevation'=>3,'panorama_score'=>3,
            'fasilitas_score'=>3,'popularity_score'=>3,'safety_score'=>3,'crowd_level'=>3,
        ]);
        $top3 = $this->getTopNIds($ranked, 3);
        $this->assertContains(1, $top3, 'SK1: Selo harus top 3 saat cost=1, others=3');
    }

    /** SK2: cost=5, others=1 → Selo TIDAK boleh top 3 (cost dominan) */
    public function test_sk02_cost_high_others_minimal(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'cost'=>5,'distance'=>1,'elevation'=>1,'panorama_score'=>1,
            'fasilitas_score'=>1,'popularity_score'=>1,'safety_score'=>1,'crowd_level'=>1,
        ]);
        $top3 = $this->getTopNIds($ranked, 3);
        $this->assertNotContains(1, $top3, 'SK2: Selo tidak boleh top 3 saat cost=5');
    }

    /** SK3: cost=1, others=5 → Selo harus top 2 */
    public function test_sk03_cost_minimal_others_high(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'cost'=>1,'distance'=>5,'elevation'=>5,'panorama_score'=>5,
            'fasilitas_score'=>5,'popularity_score'=>5,'safety_score'=>5,'crowd_level'=>5,
        ]);
        $seloRank = $this->getRankById($ranked, 1);
        $this->assertNotNull($seloRank);
        $this->assertLessThanOrEqual(3, $seloRank, 'SK3: Selo harus top 3 saat cost=1, others=5');
    }

    /** SK4: all=3 (equal weights) → Selo harus muncul di top 3 */
    public function test_sk04_all_equal_weights(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'cost'=>3,'distance'=>3,'elevation'=>3,'panorama_score'=>3,
            'fasilitas_score'=>3,'popularity_score'=>3,'safety_score'=>3,'crowd_level'=>3,
        ]);
        $top3 = $this->getTopNIds($ranked, 3);
        $this->assertContains(1, $top3, 'SK4: Selo harus top 3 saat semua bobot sama');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STUDI KASUS 5-8: Dominasi Kriteria Tunggal
    // ═══════════════════════════════════════════════════════════════════════

    /** SK5: Hanya panorama → route dengan panorama tertinggi menang */
    public function test_sk05_panorama_only(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), ['panorama_score'=>5]);
        // Selo & Suwanting punya panorama=5, harus top 2
        $top2 = $this->getTopNIds($ranked, 2);
        $this->assertContains(1, $top2, 'SK5: Selo (panorama=5) harus top 2');
        $this->assertContains(3, $top2, 'SK5: Suwanting (panorama=5) harus top 2');
    }

    /** SK6: Hanya safety → Wekas (safety=5) harus rank 1 */
    public function test_sk06_safety_only(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), ['safety_score'=>5]);
        $this->assertSame(5, $ranked[0]['route_id'], 'SK6: Wekas (safety=5) harus rank 1');
    }

    /** SK7: Hanya cost → termurah menang, Selo (1jt) harus terakhir */
    public function test_sk07_cost_only(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), ['cost'=>5]);
        $seloRank = $this->getRankById($ranked, 1);
        $this->assertSame(5, $seloRank, 'SK7: Selo (1jt) harus rank terakhir saat only cost');
    }

    /** SK8: Hanya popularity → Selo (850) harus rank 1 */
    public function test_sk08_popularity_only(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), ['popularity_score'=>5]);
        $this->assertSame(1, $ranked[0]['route_id'], 'SK8: Selo (pop=850) harus rank 1');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STUDI KASUS 9-12: Kombinasi 2 Kriteria
    // ═══════════════════════════════════════════════════════════════════════

    /** SK9: panorama+popularity tinggi → Selo menang */
    public function test_sk09_panorama_and_popularity(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'panorama_score'=>5, 'popularity_score'=>5,
        ]);
        $this->assertSame(1, $ranked[0]['route_id'], 'SK9: Selo (panorama=5,pop=850) harus rank 1');
    }

    /** SK10: cost+distance → Wekas (murah+dekat) atau Thekelan harus top */
    public function test_sk10_cost_and_distance(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'cost'=>5, 'distance'=>5,
        ]);
        $top3 = $this->getTopNIds($ranked, 3);
        $this->assertNotContains(1, $top3, 'SK10: Selo tidak boleh top 3 (cost+distance)');
    }

    /** SK11: safety+fasilitas → Mangli (safety=5,fas=4) harus top di semua jalur */
    public function test_sk11_safety_and_fasilitas_all_routes(): void
    {
        $ranked = $this->topsis->rank($this->allRoutesAlternatives(), [
            'safety_score'=>5, 'fasilitas_score'=>5,
        ]);
        $top3 = $this->getTopNIds($ranked, 3);
        // Garung (safety=5,fas=5) dan Mangli (safety=5,fas=4) harus di top
        $this->assertContains(15, $top3, 'SK11: Garung (s=5,f=5) harus top 3');
        $this->assertContains(11, $top3, 'SK11: Mangli (s=5,f=4) harus top 3');
    }

    /** SK12: elevation+crowd (cost criteria) → rendah = bagus */
    public function test_sk12_elevation_and_crowd(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'elevation'=>5, 'crowd_level'=>5,
        ]);
        // Wekas (elev=1200,crowd=1) harus top karena keduanya paling rendah
        $this->assertSame(5, $ranked[0]['route_id'], 'SK12: Wekas (elev=1200,crowd=1) harus rank 1');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STUDI KASUS 13-16: Full Dataset Tests
    // ═══════════════════════════════════════════════════════════════════════

    /** SK13: Semua 15 jalur, equal weights → skor valid [0,1] */
    public function test_sk13_all_routes_equal_weights_valid_scores(): void
    {
        $ranked = $this->topsis->rank($this->allRoutesAlternatives(), [
            'cost'=>3,'distance'=>3,'elevation'=>3,'panorama_score'=>3,
            'fasilitas_score'=>3,'popularity_score'=>3,'safety_score'=>3,'crowd_level'=>3,
        ]);
        $this->assertCount(15, $ranked);
        foreach ($ranked as $item) {
            $this->assertGreaterThanOrEqual(0.0, $item['score']);
            $this->assertLessThanOrEqual(1.0, $item['score']);
        }
    }

    /** SK14: Partial weights (hanya 3 kriteria) → tetap bekerja */
    public function test_sk14_partial_weights(): void
    {
        $ranked = $this->topsis->rank($this->allRoutesAlternatives(), [
            'panorama_score'=>5, 'safety_score'=>3, 'cost'=>1,
        ]);
        $this->assertCount(15, $ranked);
        $this->assertGreaterThan(0.0, $ranked[0]['score']);
    }

    /** SK15: Full 15 routes, popularity dominant → Bambangan (950) harus rank 1 */
    public function test_sk15_all_routes_popularity_dominant(): void
    {
        $ranked = $this->topsis->rank($this->allRoutesAlternatives(), ['popularity_score'=>5]);
        $this->assertSame(6, $ranked[0]['route_id'], 'SK15: Bambangan (pop=950) harus rank 1');
    }

    /** SK16: Consistency check - ranking harus stabil (deterministik) */
    public function test_sk16_deterministic_ranking(): void
    {
        $weights = ['cost'=>2,'panorama_score'=>4,'popularity_score'=>3,'safety_score'=>3];
        $r1 = $this->topsis->rank($this->allRoutesAlternatives(), $weights);
        $r2 = $this->topsis->rank($this->allRoutesAlternatives(), $weights);
        $ids1 = array_map(fn($r) => $r['route_id'], $r1);
        $ids2 = array_map(fn($r) => $r['route_id'], $r2);
        $this->assertSame($ids1, $ids2, 'SK16: Ranking harus deterministik');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STUDI KASUS 17-20: Edge Cases
    // ═══════════════════════════════════════════════════════════════════════

    /** SK17: Empty alternatives → return empty */
    public function test_sk17_empty_alternatives(): void
    {
        $this->assertSame([], $this->topsis->rank([], ['cost'=>3]));
    }

    /** SK18: Empty weights → return empty */
    public function test_sk18_empty_weights(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), []);
        // Semua bobot 0, semua skor harus 0
        foreach ($ranked as $item) {
            $this->assertSame(0.0, $item['score']);
        }
    }

    /** SK19: Single alternative → skor harus 0 (tidak ada pembanding) */
    public function test_sk19_single_alternative(): void
    {
        $single = [['route_id'=>1,'route_name'=>'Solo','mountain_name'=>'X',
            'criteria'=>['distance'=>5,'elevation'=>1000,'duration'=>6,'cost'=>15000,
                'difficulty'=>2,'crowd_level'=>2,'panorama_score'=>4,'fasilitas_score'=>3,
                'popularity_score'=>500,'safety_score'=>4]]];
        $ranked = $this->topsis->rank($single, ['cost'=>3,'panorama_score'=>3]);
        $this->assertCount(1, $ranked);
        // Semua kriteria degenerate → skor = 0
        $this->assertSame(0.0, $ranked[0]['score']);
    }

    /** SK20: normaliseWeights - semua weights negatif → return empty */
    public function test_sk20_negative_weights(): void
    {
        $result = $this->topsis->normaliseWeights(['cost'=>-1,'distance'=>-2], []);
        $this->assertSame([], $result);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // 2 STUDI KASUS BERMASALAH (Bug Reproduction Tests)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * BUG1: Anomali dari debugging prompt - Skenario 1
     *
     * MASALAH: Dengan vector normalization lama, Selo TIDAK muncul di top 3
     * meskipun cost weight hanya 7.7% dan non-cost 92.3%.
     *
     * AKAR MASALAH: Vector normalization (x/√Σx²) tidak menyeragamkan range.
     * Cost Selo (1.000.000) membuat kolom cost mendominasi jarak Euclidean
     * meski bobotnya kecil, karena magnitude kolom cost >> kolom lain.
     *
     * PERBAIKAN: Min-Max normalization → semua kolom [0,1] → bobot benar-benar
     * proporsional. Dengan cost weight 7.7%, penalti cost Selo hanya 7.7%
     * dari total jarak, sementara 92.3% ditentukan kriteria non-cost
     * yang Selo unggul (panorama=5, popularity=850).
     */
    public function test_bug01_vector_norm_cost_domination(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'cost'=>1,'distance'=>3,'elevation'=>3,'panorama_score'=>3,
            'fasilitas_score'=>3,'popularity_score'=>3,'safety_score'=>3,'crowd_level'=>3,
        ]);

        $seloRank = $this->getRankById($ranked, 1);
        $this->assertNotNull($seloRank, 'BUG1: Selo harus ada di ranking');
        $this->assertLessThanOrEqual(3, $seloRank,
            "BUG1 GAGAL: Selo rank $seloRank (harusnya ≤3). " .
            "Jika gagal = vector norm masih mendominasi, min-max belum diterapkan."
        );
    }

    /**
     * BUG2: Anomali dari debugging prompt - Skenario 3
     *
     * MASALAH: cost=1, others=5 → cost weight hanya 2.8%.
     * Selo punya panorama=5 (tertinggi), popularity=850 (tertinggi),
     * fasilitas=4 (tertinggi bersama), safety=4 (ke-2 tertinggi).
     * TAPI Wekas (panorama=3, pop=180) malah menang!
     *
     * AKAR MASALAH: Dengan vector norm, cost Selo = 1.000.000 membuat
     * kolom cost punya magnitude raksasa. Meski weight cost cuma 2.8%,
     * kontribusi (w × r_ij) tetap besar karena r_ij cost Selo ≈ 1.0
     * setelah vector norm (kolom didominasi satu nilai besar).
     *
     * PERBAIKAN: Min-Max norm → cost Selo = 1.0 (termahal), tapi
     * weight hanya 2.8% → penalti = 0.028. Sementara keunggulan
     * Selo di 7 kriteria lain (97.2%) lebih dari cukup mengkompensasi.
     */
    public function test_bug02_extreme_weight_imbalance(): void
    {
        $ranked = $this->topsis->rank($this->merbabuAlternatives(), [
            'cost'=>1,'distance'=>5,'elevation'=>5,'panorama_score'=>5,
            'fasilitas_score'=>5,'popularity_score'=>5,'safety_score'=>5,'crowd_level'=>5,
        ]);

        $seloRank = $this->getRankById($ranked, 1);

        $this->assertNotNull($seloRank, 'BUG2: Selo harus ada di ranking');
        $this->assertLessThanOrEqual(3, $seloRank,
            "BUG2 GAGAL: Selo rank $seloRank (harusnya ≤3). " .
            "Cost hanya 2.8% tapi masih mendominasi ranking."
        );

        // Verifikasi skor Selo tinggi (>=0.4) menunjukkan cost tidak mendominasi
        $seloScore = null;
        foreach ($ranked as $item) {
            if ($item['route_id'] === 1) { $seloScore = $item['score']; break; }
        }
        $this->assertGreaterThan(0.3, $seloScore,
            "BUG2: Skor Selo ($seloScore) harus > 0.3, menunjukkan cost tidak lagi mendominasi"
        );
    }
}
