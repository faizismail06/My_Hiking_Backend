<?php

namespace App\Console\Commands;

use App\Models\Trail;
use App\Services\GpxRouteService;
use Illuminate\Console\Command;

class ImportTrailGpx extends Command
{
    protected $signature = 'routes:import-gpx
        {route_id : ID jalur pada tabel routes}
        {gpx_file : Path file GPX}
        {--source=wikiloc : Sumber data route}
        {--max-points=1500 : Maksimal titik yang disimpan}';

    protected $description = 'Import GPX track ke routes.route_points untuk preview jalur';

    public function handle(GpxRouteService $gpxRouteService): int
    {
        $routeId = (int) $this->argument('route_id');
        $filePath = (string) $this->argument('gpx_file');
        $source = (string) $this->option('source');
        $maxPoints = max(1, (int) $this->option('max-points'));

        $trail = Trail::find($routeId);
        if (!$trail) {
            $this->error("Jalur dengan id {$routeId} tidak ditemukan.");
            return self::FAILURE;
        }

        try {
            $result = $gpxRouteService->parseFromPath($filePath, $maxPoints);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $trail->route_points = $result['points'];
        $trail->route_source = $source;
        $trail->save();

        $this->info("Import GPX berhasil untuk jalur #{$trail->id} ({$trail->nama}).");
        $this->line('Titik asli: ' . $result['original_count']);
        $this->line('Titik tersimpan: ' . $result['stored_count']);
        $this->line("Sumber: {$source}");

        return self::SUCCESS;
    }
}
