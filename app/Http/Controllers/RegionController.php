<?php
namespace App\Http\Controllers;

use App\Models\DistrictWeb;
use App\Models\RegencyWeb;
use App\Models\VillageWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class RegionController extends Controller
{
    public function getRegencies($province_id)
    {
        $provinceId = (int) $province_id;
        if ($provinceId <= 0) {
            return response()->json([]);
        }

        $regencies = Cache::remember("region.regencies.{$provinceId}", now()->addHours(12), function () use ($provinceId) {
            return RegencyWeb::query()
                ->select(['id', 'province_id', 'name'])
                ->where('province_id', $provinceId)
                ->orderBy('name')
                ->get();
        });

        return response()->json($regencies);
    }

    public function getDistricts($regency_id)
    {
        $regencyId = (int) $regency_id;
        if ($regencyId <= 0) {
            return response()->json([]);
        }

        $districts = Cache::remember("region.districts.{$regencyId}", now()->addHours(12), function () use ($regencyId) {
            return DistrictWeb::query()
                ->select(['id', 'regency_id', 'name'])
                ->where('regency_id', $regencyId)
                ->orderBy('name')
                ->get();
        });

        return response()->json($districts);
    }

    public function getVillages($district_id)
    {
        $districtId = (int) $district_id;
        if ($districtId <= 0) {
            return response()->json([]);
        }

        $villages = Cache::remember("region.villages.{$districtId}", now()->addHours(12), function () use ($districtId) {
            return VillageWeb::query()
                ->select(['id', 'district_id', 'name'])
                ->where('district_id', $districtId)
                ->orderBy('name')
                ->get();
        });

        return response()->json($villages);
    }
}
