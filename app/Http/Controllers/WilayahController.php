<?php
namespace App\Http\Controllers;

use App\Models\DistrictWeb;
use App\Models\RegencyWeb;
use App\Models\VillageWeb;
use Illuminate\Http\Request;


class WilayahController extends Controller
{
    public function getRegencies($province_id)
    {
        $regencies = RegencyWeb::where('province_id', $province_id)->get();
        return response()->json($regencies);
    }

    public function getDistricts($regency_id)
    {
        $districts = DistrictWeb::where('regency_id', $regency_id)->get();
        return response()->json($districts);
    }

    public function getVillages($district_id)
    {
        $villages = VillageWeb::where('district_id', $district_id)->get();
        return response()->json($villages);
    }
    //
}
?>
