<?php

namespace App\Http\Controllers\api;

use App\User;
use \stdClass;
use Carbon\Carbon;
use App\Models\Patient;
use App\Models\Parametre;
use App\Models\Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class ApiController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    // TYPE STRUCTURE
    public function type_structure()
    {
        $structures = Structure::where('is_delete', false)->get();
        return json_encode($structures, JSON_NUMERIC_CHECK);
    }

    // PARAMETRES
    public function parametres()
    {
        $parametres = Parametre::where('is_delete', false)->get();
        return json_encode($parametres, JSON_NUMERIC_CHECK);
    }

    // VALEURS
    public function valeurs()
    {
        $valeurs = DB::table('valeurs')
                            ->join('parametres', 'parametres.id', 'valeurs.id_parametre')
                            ->select('valeurs.*', 'parametres.libelle as libelle_parametre')
                            ->where('valeurs.is_delete', false)
                            ->get();
        return json_encode($valeurs, JSON_NUMERIC_CHECK);
    }

    // LISTE PATIENTS
    public function list_patients(Request $request)
    {
        $patients = Patient::where('is_delete', FALSE)->get();
        return json_encode($patients, JSON_NUMERIC_CHECK);
    }

    // AJOUT PATIENTS
    public function add_patients(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'sexe_patient' => 'required',
        ]);

        $birth_date = $request->birth_date;
        $patient_date = NULL;
        if($birth_date == 'yes'){
            $patient_date = $request->birth_date_know;
        }else{
            $birth_date_unknow = $request->birth_date_unknow;
            $birth_date_item = $request->birth_date_item;
            if($birth_date_item == 'day'){
                $currentDate = Carbon::now();
                $patient_date = $currentDate->subDays(intval($birth_date_unknow));

                // $day = intval(date('d'))-intval($birth_date_unknow);
                // $patient_date = date($day.'-m-Y');
            }
            else if($birth_date_item == 'month'){
                $currentDate = Carbon::now();
                $patient_date = $currentDate->subMonths(intval($birth_date_unknow));

                // $month = intval(date('m'))-intval($birth_date_unknow);
                // //dd($month);
                // $patient_date = date('d-'.$month.'-Y');
            }
            else if($birth_date_item == 'year'){
                $currentDate = Carbon::now();
                $patient_date = $currentDate->subYears(intval($birth_date_unknow));

                // $year = intval(date('Y'))-intval($birth_date_unknow);
                // $newformat = date('d-m-'.$year);
                // $patient_date = date('d-m-'.$year);
            }
        }

        Patient::create([
            'birth_date' => $patient_date,
            'name' => $request->name,
            'sexe' => $request->sexe_patient,
            'village' => $request->village_patient,
            'mother' => $request->mother_patient,
            // 'affiliation_number' => $request->affiliation_number,
            'phone_number' => $request->phone_number,
            // 'extrait_naissance' => $request->extrait_naissance,
            // 'cnib_father' => $request->cnib_father,
            // 'cnib_mother' => $request->cnib_mother,
            // 'gaspa_mother' => $request->gaspa_mother,
            'num_parent' => $request->num_telephone,
            'id_user_created' => Auth::user()->id,
        ]);

        return json_encode(['success' => 'Patient ajouté avec succès'], JSON_NUMERIC_CHECK);
    }

    // LISTE CONSULTATIONS
    public function list_consultations(Request $request)
    {
        $user = DB::table('users')
                        ->join('structures', 'users.structure_id', 'structures.id')
                        ->select('users.*', 'structures.nom_structure', 'structures.level_structure')
                        ->where('users.id', Auth::user()->id)
                        ->first();
        switch ($user->level_structure) {
            case env('LEVEL_DRS'):
                $structure = Structure::find(Auth::user()->structure_id);
                $structures = $structure->getAllChildren();
                $array = array();
                foreach ($structures as $structure) {
                    array_push($array, $structure->id);
                }
                $nconsults = DB::table('feuille_soin')->where('feuille_soin.patient_id', '>', 0)->whereIn('structures.id', $array)
                            ->join('patients', 'feuille_soin.patient_id', 'patients.id')
                            ->join('structures', 'feuille_soin.id_structure', 'structures.id')
                            ->select('feuille_soin.*', 'patients.name', 'patients.birth_date')
                            ->orderBy('feuille_soin.created_at', 'desc')
                            ->get();
                break;
            case env('LEVEL_DISTRICT'):
                $nconsults = DB::table('feuille_soin')->where('feuille_soin.patient_id', '>', 0)->where('structures.parent_id', Auth::user()->structure_id)
                            ->join('patients', 'feuille_soin.patient_id', 'patients.id')
                            ->join('structures', 'feuille_soin.id_structure', 'structures.id')
                            ->select('feuille_soin.*', 'patients.name', 'patients.birth_date')
                            ->orderBy('feuille_soin.created_at', 'desc')
                            ->get();
                break;

            default:
            $nconsults = DB::table('feuille_soin')->where('feuille_soin.patient_id', '>', 0)->where('feuille_soin.user_id', Auth::user()->id)
                            ->join('patients', 'feuille_soin.patient_id', 'patients.id')
                            ->select('feuille_soin.*', 'patients.name', 'patients.birth_date')
                            ->orderBy('feuille_soin.created_at', 'desc')
                            ->get();
                break;
        }

        $rconsults = DB::table('feuille_soin')->where('patient_id', 0)->orderBy('created_at', 'desc')->get();
        // $nconsults = DB::table('feuille_soin')->where('patient_id', '>', 0)
                               // ->join('patients', 'feuille_soin.patient_id', 'patients.id')
                               // ->select('feuille_soin.*', 'patients.name', 'patients.birth_date')
                               // ->orderBy('feuille_soin.created_at', 'desc')
                               // ->get();

        return json_encode(['rconsults' => $rconsults, 'nconsults' => $nconsults], JSON_NUMERIC_CHECK);
    }

    // AJOUT CONSULTATIONS
    public function add_consultations(Request $request){

    }

    // LISTE ORDONNANCES
    public function list_ordonnances(Request $request){

    }

    // AJOUT ORDONNANCES
    public function add_ordonnances(Request $request){

    }

    // public function map()
    // {
    //     $serie = array();

    //     /************ REGIONS ********************************/
    //     // $geom = Storage::disk('public')->get('dataville/regions.json');
    //     $geom = Storage::disk('public')->get('dataville/district.geojson');


    //     // // JSON
    //     $json_regions = json_decode($geom, true);

    //     // // Regions
    //     // $regions = $json_regions[0]['row_to_json'];
    //     $regions = $json_regions;
    //     /************ END REGIONS ********************************/

    //     // VILLES
    //     // $ville = json_encode($tab, JSON_NUMERIC_CHECK);

    //     // REGIONS
    //     $region = json_encode($regions);

    //     $data = array('region' => $regions);

    //     return json_encode($data, JSON_NUMERIC_CHECK);
    // }
}
