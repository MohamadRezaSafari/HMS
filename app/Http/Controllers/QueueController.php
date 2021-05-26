<?php

namespace App\Http\Controllers;

use App\Day_Timing;
use App\HealthcareCenterList;
use App\HospitalQueue;
use Illuminate\Http\Request;
use MohammadReza\SecurityBundle;
use App\Http\Requests;
use App\Timing;
use App\PoliclinicQueue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class QueueController extends Controller
{
	/**
	 * @var array
	 */
	private $doctorQueue_data = array();

	/**
	 * @var int
	 */
	private $doctorId;

	/**
	 * @var int
	 */
	private $timeId;

	/**
	 * @var int
	 */
	private $timeId_for_hospital;

	/**
	 * @var array
	 */
	private $hospitalQueue_data = array();

	/**
	 * @var string
	 */
	private $date_hospital;

	/**
	 * @var string
	 */
	private $date_doctor;

	/**
	 * @var bool|string
	 */
	protected $year;

	/**
	 * @var bool|string
	 */
	protected $month;

	/**
	 * @var bool|string
	 */
	protected $day;

	/**
	 * @var int
	 */
	protected $doctorId_for_whereQuery;

    /**
     * @var array
     */
	protected $_explode = array();

    /**
     * @var string
     */
	private $validDate;

	/**
	 * @var 
	 */
	private $_doctor;

	/**
	 * @var
	 */
	private $_startDate;

	/**
	 * @var
	 */
	private $_endDate;

	/**
	 * @var
	 */
	private $_startDateHospital;

	/**
	 * @var
	 */
	private $_endDateHospital;


	/**
	 * QueueController constructor.
	 */
	public function __construct()
	{
		$this->year 	= date('Y', time());
    	$this->month 	= date('m', time());
    	$this->day 		= date('d', time());
	}


	/**
	 * @return string
	 */
	protected function TrackingCode()
	{
		$code = mt_rand() . rand();
		return $code;
	}


    /**
     * @param $date
     * @return array
     */
	protected function dateSelectedExplode($date)
	{
		$this->_explode['year'] 	= date('Y', $date);
		$this->_explode['month'] 	= date('m', $date);
		$this->_explode['day'] 		= date('d', $date);

		return $this->_explode;
	}



	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function doctorQueue(Request $request)
    {
    	if($request->ajax()){

            if (\Session::has('policlinicQueueCode')){
                return response()->json(['status' => 999]);
            }

    		$security = new SecurityBundle\SBInput();

    		$this->doctorId 	= $security->getNumberInt($request->get('doctorId'));
    		$this->timeId 		= $security->getNumberInt($request->get('timeId'));
    		$this->date_doctor 	= date('Y-m-d', strtotime(Carbon::now()));
    		//$this->date_doctor 	= date('Y-m-d', strtotime(Carbon::now()));
    		$this->validDate 	= $this->dateSelectedExplode(strtotime($this->date_doctor));

    		$visitCount = Timing::where([
    				['doctor_id', '=', $this->doctorId],
    				['id', '=', $this->timeId],
    				['for', '=', 'policlinic']
    			])
    			->value('visitCount');


    		$doctorUnion = Timing::where('id', '=', $this->timeId);
				

			$doctor = $doctorUnion->value('doctor_id');

			//$this->_doctor = $doctor;


			$this->_startDate = $doctorUnion->pluck('start_date');
			$this->_endDate = $doctorUnion->pluck('end_date');

			
			$valid = Timing::where([['doctor_id', $doctor], ['for', 'policlinic']])
				->whereBetween('start_date', [date('Y-m-d', strtotime($this->_startDate)), $this->date_doctor])
				->whereBetween('end_date', [$this->date_doctor, $this->_endDate])
				//->orWhere(function ($query){
    // 				$query
    // 					->where([['doctor_id', $doctor], ['for', 'policlinic']])
    // 					->whereBetween('end_date', [date('Y-m-d', strtotime($this->_startDate)), $this->date_doctor])
    // 			})
				->count();

    		$innings = PoliclinicQueue::where('doctor_id', '=', $this->doctorId)
    			->whereDate('forDate', $this->date_doctor)
    			->max('innings');

    		if($visitCount == null){

    			return response()->json(['status' => 2]);

    		}

    		if($valid == 0){

    			return response()->json(['status' => 5]);

    		}

    		if($innings == $visitCount){

    			return response()->json(['status' => 0]);

    		}else{

    			if($this->date_doctor >= date('Y-m-d', strtotime(Carbon::now()))){

	    			$this->doctorQueue_data['forDate'] 			= $this->date_doctor;
	    			$this->doctorQueue_data['trackingCode'] 	= $this->TrackingCode();
	    			$this->doctorQueue_data['doctor_id'] 		= $this->doctorId;
	    			$this->doctorQueue_data['timings_id'] 		= $this->timeId;
	    			$this->doctorQueue_data['ip'] 				= $request->getClientIp();
	    			$this->doctorQueue_data['innings'] 			= $innings + 1;
	    			$this->doctorQueue_data['name'] 			= $security->getPersianCharacters($request->get('name'));
	    			$this->doctorQueue_data['mobile'] 			= $security->getNumberInt($request->get('mobile'));
	    			$this->doctorQueue_data['nationalCode'] 	= $security->getNumberInt($request->get('nationalCode'));
	    			$this->doctorQueue_data['nationalCode'] 	= $security->getNumberInt($request->get('nationalCode'));
					$this->doctorQueue_data['cardNumber'] 		= $security->getNumberInt($request->get('cardNumber'));

	    			session()->put('policlinicQueueCode', $this->doctorQueue_data['trackingCode']);  

	    			PoliclinicQueue::create($this->doctorQueue_data);

					return response()->json(['innings' => $this->doctorQueue_data['innings'], 'code' => $this->doctorQueue_data['trackingCode'], 'doctor' => $doctor, 'status' => 1]);

    			}else{

					return response()->json(['status' => 3]);

				}
    		}
    	}
    }








	/**
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function hospitalQueue(Request $request)
	{
		if($request->ajax()){

		    if (\Session::has('hospitalQueueCode')){
                return response()->json(['status' => 999]);
            }

			$security = new SecurityBundle\SBInput();

			$this->timeId_for_hospital 	= $security->getNumberInt($request->get('timeId'));
			//$this->date_hospital 		= date('Y-m-d', strtotime(Carbon::now() . '+ 1 days'));
			$this->date_hospital 		= date('Y-m-d', strtotime(Carbon::now()));
			$this->validDate 			= $this->dateSelectedExplode(strtotime($this->date_hospital));


			$visitCount = Timing::where([
					['id', '=', $this->timeId_for_hospital],
					['for', '=', 'hospital']
				])
				->value('visitCount');

			$doctorUnion = Timing::where('id', '=', $this->timeId_for_hospital);

			$doctor = $doctorUnion->value('doctor_id');
			$this->doctorId_for_whereQuery = $doctor;

			$innings = HospitalQueue::where('doctor_id', '=', $doctor)
				->whereDate('forDate', $this->date_hospital)
				->max('innings');

			$hospital = HealthcareCenterList::join('doctors', 'healthcare_center_lists.id', '=', 'doctors.healthcareCenterList_id')
				->select('healthcare_center_lists.id')
				->where('doctors.id', '=', $doctor)
				->value('id');

			
			$this->_startDateHospital = $doctorUnion->pluck('start_date');
			$this->_endDateHospital = $doctorUnion->pluck('end_date');

			$valid = Timing::where([['doctor_id', $doctor], ['for', 'hospital']])
				->whereBetween('start_date', [date('Y-m-d', strtotime($this->_startDateHospital)), $this->date_hospital])
				->whereBetween('end_date', [$this->date_hospital, $this->_endDateHospital])
				->count();

			if($visitCount == null){

				return response()->json(['status' => 2]);

			}

			if($valid == 0){

    			return response()->json(['status' => 5]);

    		}

			if($innings == $visitCount){

				return response()->json(['status' => 0]);

			}else{

				if($this->date_hospital >= date('Y-m-d', strtotime(Carbon::now()))){

					$this->hospitalQueue_data['doctor_id'] 		= $doctor;
					$this->hospitalQueue_data['hospital_id'] 	= $hospital;
					$this->hospitalQueue_data['timings_id'] 	= $this->timeId_for_hospital;
					$this->hospitalQueue_data['forDate'] 		= $this->date_hospital;
					$this->hospitalQueue_data['trackingCode']	= $this->TrackingCode();
					$this->hospitalQueue_data['ip'] 			= $request->getClientIp();
					$this->hospitalQueue_data['innings'] 		= $innings + 1;
					$this->hospitalQueue_data['name'] 			= $security->getPersianCharacters($request->get('name'));
					$this->hospitalQueue_data['mobile'] 		= $security->getNumberInt($request->get('mobile'));
					$this->hospitalQueue_data['nationalCode'] 	= $security->getNumberInt($request->get('nationalCode'));
					$this->hospitalQueue_data['cardNumber'] 	= $security->getNumberInt($request->get('cardNumber'));

                    \Session::put('hospitalQueueCode', $this->hospitalQueue_data['trackingCode']);

                    HospitalQueue::create($this->hospitalQueue_data);

					return response()->json(['innings' => $this->hospitalQueue_data['innings'], 'code' => $this->hospitalQueue_data['trackingCode'], 'doctor' => $doctor, 'status' => 1]);

				}else{

					return response()->json(['status' => 3]);

				}
			}
		}
	}
}