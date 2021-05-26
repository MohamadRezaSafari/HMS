<?php

namespace App\Http\Controllers;

use App\City;
use App\City_Expertise;
use App\Day;
use App\Doctor;
use App\Expertise;
use App\Gallery;
use App\HealthcareCenter;
use App\HealthcareCenterList;
use App\State;
use App\Timing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MohammadReza\SecurityBundle;

class HomeController extends Controller
{

    /**
     * @var integer
     */
    private $route_id;

    /**
     * @var integer
     */
    private $id_for_doctor_select;

    /**
     * @var integer
     */
    private $id_for_gallery;

    /**
     * @var integer
     */
    private $id_for_about;

    /**
     * @var integer
     */
    private $expertise_id_for_doctor;

    /**
     * @var integer
     */
    private $healthcareCenterName_id;

    /**
     * @var integer
     */
    private $expertise_id_for_doctor_paginate;

    /**
     * @var integer
     */
    private $stateId_for_city;

    /**
     * @var integer
     */
    private $cityId_for_expertise;

    /**
     * @var integer
     */
    private $doctorId_page;

    /**
     * @var integer
     */
    private $cityId_for_custom_hospital;

    /**
     * @var integer
     */
    private $expertise_id_for_state;

    /**
     * @var integer
     */
    private $state_id_for_city_section1;

    /**
     * @var integer
     */
    private $city_id_for_hospital_section1;

    /**
     * @var integer
     */
    private $id_for_expertise_search;

    /**
     * @var integer
     */
    private $id_for_expertise_search_paginate;


    /**
     * @var string
     */
    private $text_for_search_hospital_expertise;

    /**
     * @var integer
     */
    private $id_text_for_search_hospital_expertise;


    /**
     * @var string
     */
    private $txt_for_expertise;


    /**
     * @var string
     */
    private $expertiseSearchRouting;




    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $expertise = Expertise::pluck('expertiseName', 'id');
        $state = State::pluck('stateName', 'id');

        return view('home', compact('expertise', 'state'));
    }


    /**
     * @param $healthcareCenter
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search($healthcareCenter, $id)
    {
        $this->route_id = (new SecurityBundle\SBInput())->getNumberInt($id);

        //$list = HealthcareCenterList::where('healthcareCenter_id', '=', $this->route_id)->get();
        $doctor = Doctor::where('clinicStatus', '=', 1)->get();

        return view('search', compact('doctor'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hlist($id)
    {
        $this->route_id = (new SecurityBundle\SBInput())->getNumberInt($id);

        $list = HealthcareCenterList::where('id', '=', $this->route_id)->get();

        return view('home.hlist', compact('list'));
    }


    /**
     * @param $id
     * @param $name
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function expertiseSearch($id, $name, $cityId = null)
    {
        $this->healthcareCenterName_id = HealthcareCenter::where('healthcareCenterName', '=', 'بیمارستان')->pluck('id')->first();
        $this->id_for_expertise_search = (new SecurityBundle\SBInput())->getNumberInt($cityId);
        $this->expertise_id_for_doctor = (new SecurityBundle\SBInput())->getNumberInt($id);
        $this->txt_for_expertise = (new SecurityBundle\SBInput())->getPersianCharacters(str_replace("_", " ", $name));

        if($this->id_for_expertise_search == null){

            $doctor = Doctor::join('healthcare_center_lists', 'doctors.healthcareCenterList_id', '=', 'healthcare_center_lists.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('doctors.*', 'healthcare_center_lists.healthcareCenterListName', 'healthcare_center_lists.tell', 'healthcare_center_lists.address','expertises.expertiseName')
                ->where([
                    ['doctors.expertise_id', '=', $this->expertise_id_for_doctor],
                    ['doctors.confirm', '=', 1]
                ])
                ->orWhere('expertises.expertiseName', 'like', "%$this->txt_for_expertise%")
                ->get();

            return view('expertise-search.expertise-null', compact('doctor'));

        }else{

            $doctor = Doctor::join('healthcare_center_lists', 'doctors.healthcareCenterList_id', '=', 'healthcare_center_lists.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('doctors.*', 'healthcare_center_lists.healthcareCenterListName', 'healthcare_center_lists.tell', 'healthcare_center_lists.address','expertises.expertiseName')
                ->where([
                    ['doctors.expertise_id', '=', $this->expertise_id_for_doctor],
                    ['doctors.city_id', '=', $this->id_for_expertise_search ],
                    ['doctors.confirm', '=', 1]
                ])->paginate(10);

            //$hcl_id = 
                
            return view('expertise-search.expertise', compact('doctor'));
        }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function expertiseSearchPaginate(Request $request)
    {
        $this->healthcareCenterName_id = HealthcareCenter::where('healthcareCenterName', '=', 'بیمارستان')->pluck('id')->first();
        $this->expertise_id_for_doctor_paginate = (new SecurityBundle\SBInput())->getNumberInt($request->get('id'));
        $this->id_for_expertise_search_paginate = (new SecurityBundle\SBInput())->getNumberInt($request->get('cityId'));
        $doctor = Doctor::join('healthcare_center_lists', 'doctors.healthcareCenterList_id', '=', 'healthcare_center_lists.id')
            ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
            ->select('doctors.*', 'healthcare_center_lists.*', 'expertises.expertiseName')
            ->where([
                ['doctors.expertise_id', '=', $this->expertise_id_for_doctor_paginate],
                ['doctors.city_id', '=', $this->id_for_expertise_search_paginate ]
            ])->paginate(10);
        if($request->ajax()){
            return response()->json(view('expertise-search.paginate', compact('doctor'))->render());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bundleAjaxCityList(Request $request)
    {
        if($request->ajax()){
            $this->stateId_for_city = (new SecurityBundle\SBInput())->getNumberInt($request->get('id'));
            //$city = City::where('id', '=', $this->stateId_for_city)->pluck('cityName', 'id');
            $city = City::where('state_id', '=', $this->stateId_for_city)
                ->select('cityName', 'id')
                ->get();
            if($city->isEmpty())
                return response()->json(view('empty')->render());
            else
                return response()->json(view('first.cityList', compact('city'))->render());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bundleAjaxExpertiseList(Request $request)
    {
        if($request->ajax()){
            $this->cityId_for_expertise = (new SecurityBundle\SBInput())->getNumberInt($request->get('id'));
            $expertise = Expertise::join('city_expertise', 'expertises.id', '=', 'city_expertise.expertise_id')
                ->where('city_expertise.city_id', '=', $this->cityId_for_expertise)
                ->select('expertises.*', 'city_expertise.city_id')
                ->get();
            $hospital = HealthcareCenterList::where([
                    ['city_id', '=', $this->cityId_for_expertise],
                    ['healthcareCenter_id', '=', 1]
                ])->get();
            $clinic = HealthcareCenterList::where([
                    ['city_id', '=', $this->cityId_for_expertise],
                    ['healthcareCenter_id', '=', 2]
                ])->get();

            if($expertise->isEmpty())
                return response()->json(view('empty')->render());
            else
                return response()->json(view('first.expertiseList-healthCenterList', compact('expertise', 'hospital', 'clinic'))->render());
        }
    }


    /**
     * @param $id
     * @param $name
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function doctorsSearch($id, $name)
    {
        $this->doctorId_page = (new SecurityBundle\SBInput())->getNumberInt($id);

        $doctor = Doctor::join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
            ->select('doctors.*', 'expertises.expertiseName')
            ->where([
                ['doctors.id', '=', $this->doctorId_page],
                ['doctors.clinicStatus', '=', 1]
            ])
            ->get();   

        $time = Timing::where([
                ['doctor_id', '=', $this->doctorId_page],
                ['for', '=', 'policlinic']
            ])
            ->get();
            
        if (count($doctor) > 0) {
        	return view('doctors.search', compact('doctor', 'time'));		
        }else{
        	abort(404);
        }
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hospitalCity($id, $expertiseSearch = null)
    {
        $this->cityId_for_custom_hospital = (new SecurityBundle\SBInput())->getNumberInt($id);
        $this->expertiseSearchRouting = (new SecurityBundle\SBInput())->getPersianCharacters(str_replace("_", " ", $expertiseSearch));
        if($this->expertiseSearchRouting == null){
            $hospitals = HealthcareCenterList::where([
                ['city_id', '=', $this->cityId_for_custom_hospital],
                ['healthcareCenter_id', '=', 1]
            ])->get();
        }else{
            $hospitals = HealthcareCenterList::where([
                ['city_id', '=', $this->cityId_for_custom_hospital],
                ['healthcareCenter_id', '=', 1],
                ['expertise', 'like',  "%$this->expertiseSearchRouting%" ]
            ])->get();
        }
// dd($this->cityId_for_custom_hospital);
        return view('hospital-city.custom', compact('hospitals'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bundleAjaxStateList(Request $request)
    {
        if($request->ajax()){
            $this->expertise_id_for_state = (new SecurityBundle\SBInput())->getNumberInt($request->get('id'));
            $state = City_Expertise::join('cities', 'city_expertise.city_id', '=', 'cities.id')
                ->join('states', 'states.id', '=', 'cities.state_id')
                ->select('states.*')
                ->where('city_expertise.expertise_id', '=', $this->expertise_id_for_state)
                ->select('states.stateName', 'states.id')
                ->get();

            session()->forget('keyID');
            session()->put('keyID', $this->expertise_id_for_state);    

            if($state->isEmpty())
                return response()->json(view('empty')->render());
            else
                return response()->json(view('first.StateList', compact('state'))->render());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bundleAjaxCityListForExpertise(Request $request)
    {
        if($request->ajax()){
            $this->state_id_for_city_section1 = (new SecurityBundle\SBInput())->getNumberInt($request->get('id'));
            $city = City::join('city_expertise', 'city_expertise.city_id', '=', 'cities.id')
                ->where('cities.state_id', '=', $this->state_id_for_city_section1)
                ->pluck('cities.cityName', 'cities.id');
                //->get();

            if($city->isEmpty())
                return response()->json(view('empty')->render());
            else
                return response()->json(view('first.CityListSectionOne', compact('city'))->render());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bundleAjaxHospitalListForExpertise(Request $request)
    {
        if($request->ajax()){
            $this->city_id_for_hospital_section1 = (new SecurityBundle\SBInput())->getNumberInt($request->get('id'));
            $this->id_text_for_search_hospital_expertise = (new SecurityBundle\SBInput())->getNumberInt($request->get('txt'));
            $this->text_for_search_hospital_expertise  = Expertise::where('id', '=', $this->id_text_for_search_hospital_expertise)
                ->value('expertiseName');
            $hospital = HealthcareCenterList::where([
                ['city_id', '=', $this->city_id_for_hospital_section1],
                ['healthcareCenter_id', '=', 1],
                ['expertise', 'like',  "%$this->text_for_search_hospital_expertise%" ]
            ])->get();

            $_expertise = $this->text_for_search_hospital_expertise;
            $_cityId = $this->city_id_for_hospital_section1;
            session()->forget('keyID');
            
            if($hospital->isEmpty())
                return response()->json(view('empty')->render());
            else
                return response()->json(view('first.hospitalSectionOne', compact('hospital', '_expertise', '_cityId'))->render());
        }
    }
}