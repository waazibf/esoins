<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Structure;
use App\Models\Acte;
use App\Models\Equipement;
use App\Models\Product;
use App\Models\Nproduct;
use App\Models\Patient;
use App\Models\Org_unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getStat()
    {
        $total_act = 0;
        $total_eq = 0;
        $total_med = 0;
        $total_ev = 0;

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

                $consults = DB::table('feuille_soin')
                                    ->join('structures', 'structures.id', 'feuille_soin.id_structure')
                                    ->whereIn('structures.id', $array)
                                    ->get();
                break;
            case env('LEVEL_DISTRICT'):
                $consults = DB::table('feuille_soin')
                                    ->join('structures', 'structures.id', 'feuille_soin.id_structure')
                                    ->where('structures.parent_id', Auth::user()->structure_id)
                                    ->get();

                break;

            default:
                $consults = DB::table('feuille_soin')->where('user_id', Auth::user()->id)->get();
                break;
        }

        foreach($consults as $consult){
            $total_act = $total_act + Acte::whereIn('code_acte', explode(" ", $consult->liste_act))->get()->sum('price_pvp');
            $total_eq = $total_eq + Equipement::whereIn('code_equipement', explode(" ", $consult->liste_eq ))->get()->sum('unit_cost_pvp');
            $total_med = $total_med + Product::whereIn('code_product', explode(" ", $consult->liste_prod))->get()->sum('prix_pvp');
            $total_ev = $total_ev + Acte::whereIn('code_acte', explode(" ", $consult->liste_act))->get()->sum('price_pvp');
        }
        $response['data'] = array('total_act'=>floatval(round($total_act)), 'total_eq'=>floatval(round($total_eq)), 'total_med'=>floatval(round($total_med)), 'total_ev'=>floatval(round($total_ev)));
        return response()->json($response);
    }

    // FILTER DATA
    public function dataFilter(Request $request)
    {
        // INIT VALUE
        $total_act = 0;
        $total_eq = 0;
        $total_med = 0;
        $total_ev = 0;
        // STRUCTURE SELEECT
        $id_drs_filtre = $request->id_drs_filtre;
        $id_district_filtre = $request->id_district_filtre;
        $id_csps_filtre = $request->id_csps_filtre;

        if($id_csps_filtre){
            $consults = DB::table('feuille_soin')->where('id_structure', $id_csps_filtre)->get();
            $structure = Structure::where('id', $id_csps_filtre)->first();
        }elseif($id_district_filtre){
            $consults = DB::table('feuille_soin')
                                    ->join('structures', 'structures.id', 'feuille_soin.id_structure')
                                    ->where('structures.parent_id', $id_district_filtre)
                                    ->get();
            $structure = Structure::where('id', $id_district_filtre)->first();

        }else{
            $structure = Structure::find($id_drs_filtre);
            $structures = $structure->getAllChildren();
            $array = array();
            foreach ($structures as $structure) {
                array_push($array, $structure->id);
            }

            $consults = DB::table('feuille_soin')
                                ->join('structures', 'structures.id', 'feuille_soin.id_structure')
                                ->whereIn('structures.id', $array)
                                ->get();
            $structure = Structure::where('id', $id_drs_filtre)->first();
        }

        foreach($consults as $consult){
            $total_act = $total_act + Acte::whereIn('code_acte', explode(" ", $consult->liste_act))->get()->sum('price_pvp');
            $total_eq = $total_eq + Equipement::whereIn('code_equipement', explode(" ", $consult->liste_eq ))->get()->sum('unit_cost_pvp');
            $total_med = $total_med + Product::whereIn('code_product', explode(" ", $consult->liste_prod))->get()->sum('prix_pvp');
            $total_ev = $total_ev + Acte::whereIn('code_acte', explode(" ", $consult->liste_act))->get()->sum('price_pvp');
        }

        $response['data'] = array('total_act'=>floatval(round($total_act)), 'total_eq'=>floatval(round($total_eq)), 'total_med'=>floatval(round($total_med)), 'total_ev'=>floatval(round($total_ev)), 'org_unit_name'=>$structure->nom_structure);
        return response()->json($response);
    }

    public function econsultation()
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
        return view('esoins.consultation', compact('rconsults', 'nconsults'));
    }

    public function efiche($id)
    {
        $prod1 = Produit::create([
            'id'=>87,
            'nom_produit'=>'Artésunate20 mg Pyronaridine 60 mg Sachets',
            'prix_pvp'=>'0.00',
        ]);

        $prod2 = Produit::create([
            'id'=>88,
            'nom_produit'=>'Artésunate 60mg Pyronaridine 180 mg Comprimés',
            'prix_pvp'=>'0.00',
        ]);

        $prod1->update([
            'code_product'=>'prod'.$prod1->id,
        ]);
        $prod2->update([
            'code_product'=>'prod'.$prod2->id,
        ]);
        // CONSULTATION
        $consult = DB::table('feuille_soin')->where('id', $id)->first();

        // PATIENT
        $patient = Patient::where('id', $consult->patient_id)->first();

        // CSPS
        $csps = Structure::where('id', Auth::user()->structure_id)->first();

        // DISTRICT
        $district = Structure::where('id', $csps->parent_id)->first();

        // DRS
        $drs = Structure::where('id', $district->parent_id)->first();
        // $nproducts = DB::table('netsigl')->where('ordonnnce_id', $consult->num_ordonance)->get();
        // NETSIGL PRODUCT
        $nproducts = DB::table('netsigl')
                                ->join('products', 'netsigl.code_product', 'products.code_product')
                                ->select('products.*', 'netsigl.quantity_product as quantity_product')
                                ->where('netsigl.ordonnance_id', $consult->num_ordonance)
                                ->get();
        // $cproducts = DB::table('netsigl')->where('ordonnnce_id', $consult->num_ordonance)->get();
        // CONSULTATION PRODUCT
        $liste_prod =  explode(" ",$consult->liste_prod);
        $quantity_prod =  explode(" ",$consult->quantity_prod);
         // if($consult->patient_id == 0){
             // $cproducts = Product::whereIn('code_product', $liste_prod)->get();
         // }else{
             // $cproducts = Nproduct::whereIn('uid', $liste_prod)->get();
         // }

        $cproducts = Product::whereIn('code_product', $liste_prod)->get();
        // dd($cproducts);

        // $cproducts = DB::table('feuille_soin')
                                // ->join('products', 'feuille_soin.code_product', 'products.uid')
                                // ->select('products.*')
                                // ->where('feuille_soin.ordonnance_id', $consult->num_ordonance)
                                // ->get();

        // ACTES
        $liste_act =  explode(" ",$consult->liste_act);
        $quantity_act =  explode(" ",$consult->quantity_act);
        $actes = Acte::whereIn('code_acte', $liste_act)->get();

        // EQUIPEMENT
        $liste_eq = explode(" ",$consult->liste_eq);
        $quantity_eq = explode(" ",$consult->quantity_eq);
        $equipements = Equipement::whereIn('code_equipement', $liste_eq)->get();
        return view('esoins.fiche', compact('consult', 'nproducts', 'cproducts', 'actes', 'equipements', 'patient', 'quantity_prod', 'quantity_act', 'quantity_eq', 'csps', 'district', 'drs'));
    }

    /******** DISPENSATION ****************** */
    public function dispensation()
    {
        // $ordonnances = DB::table('netsigl')->where('ordonnance_id', '!=', '')->get();
        $nordonnances = DB::table('netsigl')
                                ->join('nproducts', 'netsigl.code_product', 'nproducts.code_product')
                                ->select('netsigl.*', 'nproducts.drd_price', 'nproducts.name')
                                ->where('netsigl.ordonnance_id', '!=', '')
                                ->get();
        $ordonnances = DB::table('netsigl')
                                ->join('products', 'netsigl.code_product', 'products.code_product')
                                ->select('netsigl.*', 'products.prix_drd', 'products.name')
                                ->where('netsigl.ordonnance_id', '!=', '')
                                ->get();
        // $ordonnances = DB::table('netsigl')->where('ordonnance_id', '!=', '')->get();

        return view('esoins.dispensation', compact('nordonnances', 'ordonnances'));
    }

    /**************** SELECT CHARGEMENT DES SOUS TABLES *************************/
    public function selection(Request $request)
    {
        $idparent_val = $request->idparent_val;
        $table = $request->table;

        $array[] = array("id" => '', "name" => '');

        switch ($table) {
            case 'structure':
            $structurees = Structure::where(['is_delete'=>FALSE, 'parent_id'=>$idparent_val])->get();
                foreach ($structurees as $structuree)
                {
                    $array[] = array("id" => $structuree->id, "name" => $structuree->nom_structure);
                }
            break;

            case 'valeur':
                $valeurs = Valeur::where(['is_delete'=>FALSE, 'id_parent'=>$idparent_val])->get();
                foreach ($valeurs as $valeur)
                {
                    $array[] = array("id" => $valeur->id, "name" => $valeur->libelle);
                }
                break;

            case 'arrondissement':
                $arrondissements = Arrondissement::where(['is_delete'=>FALSE, 'id_commune'=>$idparent_val])->get();
                foreach ($arrondissements as $arrondissement)
                {
                    $array[] = array("id" => $arrondissement->id, "name" => $arrondissement->libelle);
                }
                break;
        }

        $response['data'] = $array;
        return response()->json($response);
    }

    /**************** SELECT CHARGEMENT DES SOUS TABLES *************************/

    /**
     * download json data localite
     */
    public function getjson()
    {
        $serie = array();
        $org_units = Org_unit::where('is_delete', FALSE)->where('parent_one', 'like', '%Tenado%')->limit(100)->get();

        $tab = new \StdClass();
        $tab->type = "FeatureCollection";

        foreach ($org_units as $org_unit) {

            // GEOM
            $long = str_replace(',', '.', $org_unit->longitude);
            $lat = str_replace(',', '.', $org_unit->latitude);

            $properties = new \StdClass();
            $properties->code = $org_unit->code;
            $properties->nom = $org_unit->nom;

            $feature = new \StdClass();
            $feature->type = "Feature";
            $feature->properties = $properties;

            $geometrie = new \StdClass();
            $geometrie->type = "Point";
            $geometrie->coordinates = [$long, $lat];
            $feature->geometry = $geometrie;

            $tab->features[] = $feature;
        }

        /************ REGIONS ********************************/
        // $geom = Storage::disk('public')->get('dataville/regions.json');
        $geom = Storage::disk('public')->get('dataville/district.geojson');


        // // JSON
        $json_regions = json_decode($geom, true);
    //    $data = response()->json($json_regions);
    //    dd($data);
        // dd($json_regions);

        // // Regions
        // $regions = $json_regions[0]['row_to_json'];
        $regions = $json_regions;
        /************ END REGIONS ********************************/

        // ORG UNITS
        $orgUnit = json_encode($tab, JSON_NUMERIC_CHECK);

        // REGIONS
        $region = json_encode($regions);

        $data = array('region' => $regions, 'org_unit'=>$orgUnit);

        return json_encode($data, JSON_NUMERIC_CHECK);
    }

    // LISTE OF PRODUCT
    public function ordonnance()
    {
        $nproducts = Nproduct::all();
        return view('esoins.ordannance', compact('nproducts'));
    }

    // SAVE ORDONNANCE
    public function save(Request $request)
    {
        $this->validate($request, [
            'numero' => 'required|unique:netsigl,ordonnance_id',
            'date_dispensation' => 'required',
        ]);
        // $nproducts = Nproduct::all();
        // List prod
        $array_prod = array();
        $tab_prod = explode(',',  $request->liste_prod);
        for($i=0;$i<count($tab_prod); $i++){
            if(strlen($tab_prod[$i])>0){
                array_push($array_prod, $tab_prod[$i]);
            }

        }

        // Quantity prod
        $arrayquantity_prod = array();
        $tabquantity_prod = explode(',',  $request->quantity_prod);
        for($i=0;$i<count($tabquantity_prod); $i++){
            if(strlen($tabquantity_prod[$i])>0){
                array_push($arrayquantity_prod, $tabquantity_prod[$i]);
            }

        }



        $structure = Structure::findOrFail(Auth::user()->structure_id);
        // $product =  explode(",",$request->product);
        // $quantity =  explode(",",$request->quantity);
        // $product_id_list =  explode(",",$request->product_id_list);
        // dd($quantity);
        for($i=0; $i<count($array_prod); $i++){
            $productvalue = $array_prod[$i];
            $quantityvalue = $arrayquantity_prod[$i];
            // dd($quantityvalue);
            DB::insert('insert into netsigl (ordonnance_id, name_product, orgunit_id, orgunit_name, date_dispensation, quantity_product, code_product, user_id) values (?, ?, ?, ?, ?, ?, ?, ?)',
                [$request->numero, '', $structure->code_structure, '', $request->date_dispensation,  $quantityvalue, $productvalue, Auth::user()->id]);
        }
        // dd($arrayquantity_prod);
        // dd($product_id_list);

        // for($i=0; $i<count($quantity); $i++){
            // if(is_null($quantity[$i])){
                // array_splice($quantity, $i, 1);
            // }
        // }

       //  for($i=0; $i<count($request->product); $i++){
            //DB::insert('insert into netsigl (ordonnnce_id, orgunit_id, date_dispensation, drugs_dispensation, orgunit_name, code_product, quantity_product) values (?, ?, ?, ?, ?, ?, ?)',
        // [$request->numero, $request->numero, $request->date_dispensation, '', '', $product[$i], $quantity[$i]]);
        // }


        return redirect()->route('esoins.dispensation');
    }

    public function addConsultation(Request $request, $id)
    {
        $patient = Patient::where('id', $id)->first();
        $products = Product::all();
        $actes = Acte::all();
        $equipements = Equipement::all();
        return view('esoins.add-consultation', compact('patient', 'products', 'actes', 'equipements'));
    }

    public function storeConsultation(Request $request)
    {
        // dd($request->all());
        // List prod
        $array_prod = array();
        $tab_prod = explode(',',  $request->liste_prod);
        for($i=0;$i<count($tab_prod); $i++){
            if(strlen($tab_prod[$i])>0){
                array_push($array_prod, $tab_prod[$i]);
            }

        }
        $liste_prod = implode(" ", $array_prod);

        // List act
        $array_act = array();
        $tab_act = explode(',',  $request->liste_act);
        for($i=0;$i<count($tab_act); $i++){
            if(strlen($tab_act[$i])>0){
                array_push($array_act, $tab_act[$i]);
            }

        }
        $liste_act = implode(" ", $array_act);

        // List eq
        $array_eq = array();
        $tab_eq = explode(',',  $request->liste_eq);
        for($i=0;$i<count($tab_eq); $i++){
            if(strlen($tab_eq[$i])>0){
                array_push($array_eq, $tab_eq[$i]);
            }

        }
        $liste_eq = implode(" ", $array_eq);

        // Quantity prod
        $arrayquantity_prod = array();
        $tabquantity_prod = explode(',',  $request->quantity_prod);
        for($i=0;$i<count($tabquantity_prod); $i++){
            if(strlen($tabquantity_prod[$i])>0){
                array_push($arrayquantity_prod, $tabquantity_prod[$i]);
            }

        }
        $quantity_prod = implode(" ", $arrayquantity_prod);

        // Quantity act
        $arrayquantity_act = array();
        $tabquantity_act = explode(',',  $request->quantity_act);
        for($i=0;$i<count($tabquantity_act); $i++){
            if(strlen($tabquantity_act[$i])>0){
                array_push($arrayquantity_act, $tabquantity_act[$i]);
            }

        }
        $quantity_act = implode(" ", $arrayquantity_act);

        // Quantity eq
        $arrayquantity_eq = array();
        $tabquantity_eq = explode(',',  $request->quantity_eq);
        for($i=0;$i<count($tabquantity_eq); $i++){
            if(strlen($tabquantity_eq[$i])>0){
                array_push($arrayquantity_eq, $tabquantity_eq[$i]);
            }

        }
        $quantity_eq = implode(" ", $arrayquantity_eq);

        $cout_mise_en_observation = 0;
        if($request->cout_mise_en_observation !=''){
            $cout_mise_en_observation = $request->cout_mise_en_observation;
        }
        // dd($liste_eq);
        // dd(explode(',',  $request->liste_prod));
        $diagnostic = array();
        if($request->diagnostic){
            $diagnostic = $request->diagnostic;
        }
        $patient = Patient::where('id', $request->patient_id)->first();
        $diagnostic = implode(" ", $diagnostic);

        // dd($request->cout_total_prod.' '.$request->cout_total_act.' '.$request->cout_tatol_equipement);
        DB::table('feuille_soin')->insertOrIgnore([
            'id' => Time(),
            'consultation_type' => $request->consultation_type,
            'cout_mise_en_observation' => $cout_mise_en_observation,
            'cout_total_eq' => $request->cout_tatol_equipement,
            'cout_total_act' => $request->cout_total_act,
            'cout_total_prod' => $request->cout_total_prod,
            'registre_number' => $request->registre_number,
            'serie_number' => $request->serie_number,
            'visit_date' => $request->consultation_date,
            'sex' => $patient->sexe,
            // 'csps' => $request->,
            // 'district' => $request->,
            // 'drs' => $request->,
            'liste_eq' => $liste_eq,
            'nom_agent' => Auth::user()->name,
            'nom_patient' => $patient->name,
            'num_ordonance' => $request->num_ordonance,
            // 'qualification' => $request->,
            // 'screen_act_type' => $request->,
            'liste_prod' => $liste_prod,
            'liste_act' => $liste_act,
            'quantity_prod' => $quantity_prod,
            'quantity_act' => $quantity_act,
            'quantity_eq' => $quantity_eq,
            'user_id' => Auth::user()->id,
            'patient_id' => $request->patient_id,
            'diagnostic' => $diagnostic,
            'id_structure' => Auth::user()->structure_id,
        ]);
    }

    public function checkOrdonnance(Request $request)
    {
        $consult = DB::table('feuille_soin')->where('num_ordonance', $request->numero)->first();
        $liste_prod =  explode(" ",$consult->liste_prod);
        // if($consult->patient_id == 0){
             // $products = Product::whereIn('code_product', $liste_prod)->get();
         // }else{
             // $products = Nproduct::whereIn('uid', $liste_prod)->get();
         // }

        $products = Product::whereIn('code_product', $liste_prod)->get();

        response()->json($products);
        return view("esoins.ckeck-ordonnance", compact("products"));
    }


    public function datanetsigl()
    {
        // sendRequest
        $url = "https://burkinanetsigl.com/api/36/events.json?orgUnit=Ize8xlE9AdQ&program=pYGIlffbGeL&startDate=2023-07-20&endDate=2023-07-26";
        $url = "https://burkinanetsigl.com/api/36/events.json?fields=*&orgUnit=zmSNCYjqQGj&ouMode=DESCENDANTS&program=pYGIlffbGeL&fields=trackedEntityInstance,dataValues[dataElement,value]&lastUpdateDuration=5min&paging=false";

        $username = 'admin';

        $password = 'PQGyzV6LMNUUxSrXz2F-q';

        $ch = curl_init ();

        $headr = array ();

        $headr [] = 'Content-type: application/json';

        $headr [] = 'Authorization: Basic ' . base64_encode ( $username . ":" . $password );

        curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE );

        curl_setopt ( $ch, CURLOPT_URL, $url );

        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headr );

        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );

        //curl_setopt ($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );

        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );

        $data = curl_exec($ch);

        //$body = curl_exec($ch);

        if ($data === false)
        {
            // throw new Exception('Curl error: ' . curl_error($crl));

            print_r ( 'Curl error: ' . curl_error ( $ch ) );
        }
        //else {echo "Connexion reussi".PHP_EOL;}

        curl_close ( $ch );

        $datas = json_decode ( $data );
        $ordonnnce_id = NULL;
        $name_product = NULL;
        $orgunit_id = NULL;
        $orgunit_name = NULL;
        $date_dispensation = NULL;
        $quantity_product = NULL;
        $code_product = NULL;
        $type_transaction = NULL;
        // $drugs_dispensation = NULL;




        // return response()->json($datas);
        for($i=0; $i<count($datas->events); $i++){
            $datae = ($datas->events)[$i];
            // return response()->json($datae);
            $dataElement = $datae->dataValues;
            $k = 0;
            // return response()->json($dataElement);
            for($k=0; $k<count($dataElement); $k++){
                // NUMERO ORDONNANCE
                if($dataElement[$k]->dataElement == env('ordonnnce_id')){
                    $ordonnnce_id = $dataElement[$k]->value;
                }

                // NOM DU PRODUIT
                if($dataElement[$k]->dataElement == env('name_product')){
                    $name_product = $dataElement[$k]->value;
                }

                // ORG UNIT ID
                $orgunit_id = $datae->orgUnit;

                // ORG UNIT NAME
                $orgunit_name = $datae->orgUnitName;

                // DATE DISPENSATION
                if($dataElement[$k]->dataElement == env('date_dispensation')){
                    $date_dispensation = $dataElement[$k]->value;
                }

                // QUANTITE PRODUIT
                if($dataElement[$k]->dataElement == env('quantity_product')){
                    $quantity_product = $dataElement[$k]->value;
                }

                // CODE PRODUIT
                if($dataElement[$k]->dataElement == env('code_product')){
                    $code_product = $dataElement[$k]->value;
                }

                // TYPE TRANSACTION
                if($dataElement[$k]->dataElement == env('type_transaction')){
                    $type_transaction = $dataElement[$k]->value;
                }
            }
            if($type_transaction == 4){
                // dd($code_product);
                $netsigl = DB::table("netsigl")->where('ordonnance_id', $ordonnnce_id)->get();
                // if(count($netsigl)<=0){
                    DB::insert('insert into netsigl (ordonnance_id, name_product, orgunit_id, orgunit_name, date_dispensation, quantity_product, code_product) values (?, ?, ?, ?, ?, ?, ?)',
                    [$ordonnnce_id, $name_product, $orgunit_id, $orgunit_name, $date_dispensation,  $quantity_product, $code_product]);
                // }


            }
            // return response()->json($dataElement);


            // return $datae;
        }
    }
}
