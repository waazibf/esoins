<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\User;
use Illuminate\Validation\ValidationException;
use \stdClass;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    // TYPE STRUCTURE
    public function map()
    {
        $serie = array();

        /************ REGIONS ********************************/
        // $geom = Storage::disk('public')->get('dataville/regions.json');
        $geom = Storage::disk('public')->get('dataville/district.geojson');


        // // JSON
        $json_regions = json_decode($geom, true);

        // // Regions
        // $regions = $json_regions[0]['row_to_json'];
        $regions = $json_regions;
        /************ END REGIONS ********************************/

        // VILLES
        // $ville = json_encode($tab, JSON_NUMERIC_CHECK);

        // REGIONS
        $region = json_encode($regions);

        $data = array('region' => $regions);

        return json_encode($data, JSON_NUMERIC_CHECK);
    }
}
