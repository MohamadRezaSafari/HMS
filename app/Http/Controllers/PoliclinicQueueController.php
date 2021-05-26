<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MohammadReza\SecurityBundle;
use App\Http\Requests;
use App\PoliclinicQueue;
use App\Hcl_User;
use Carbon\Carbon;
use App\Doctor;
use App\Expertise;
use App\Timing;
use App\HospitalQueue;
use App\HealthcareCenterList;

class PoliclinicQueueController extends Controller
{
    /**
     * @var bool|string
     */
	protected $today;

    /**
     * @var string
     */
	private $txtSearchAll;

    /**
     * @var int
     */
    protected $range;

    /**
     * @var array
     */
    protected $dayRange = array();

    /**
     * @var array
     */
    private $cancel = array();

    /**
     * @var string
     */
    private $cancelTime;

    /**
     * @var int
     */
    private $returnAmount;

    /**
     * @var string
     */
    private $activeVisits;

    /**
     * @var array
     */
    private $showVisits = array();


    /**
     * @return mixed
     */
    private function doctorID()
    {
        return Doctor::join('doctor_user', 'doctor_user.doctor_id', '=', 'doctors.id')
            ->select('doctors.id')
            ->where([
                ['doctor_user.user_id', '=', \Auth::user()['id']]
            ])
            ->first();
    }


    /**
     * @param $day
     * @return array
     */
    protected function dayRangeWeek($day)
    {
        $this->range = getdate(strtotime($day))['mday']; 

        if($this->range <= 7 || $this->range == 31){

            $this->dayRange['start'] = 1;
            $this->dayRange['end'] = 7;

        }

        else if($this->range <= 14){

            $this->dayRange['start'] = 7;
            $this->dayRange['end'] = 14;
            
        }

        else if($this->range <= 21){

            $this->dayRange['start'] = 14;
            $this->dayRange['end'] = 21;
            
        }

        else if($this->range <= 28){

            $this->dayRange['start'] = 21;
            $this->dayRange['end'] = 29;
            
        }
        
        return $this->dayRange;
    }


    /**
     * @param $date
     * @return mixed
     */
	protected function _month($date)
    {  
        return getdate($date)['mon'];
    }

    
    /**
     * @param $date
     * @return mixed
     */
    protected function _monthAgo($date)
    {  
        return getdate($date)['mon'] - 1;
    }


    /**
     * PoliclinicQueueController constructor.
     */
	public function __construct()
    {
    	$this->middleware(['auth', 'roleDoctor']);

        if (\Cookie::get('a5e0a7e4550411d6')) {
            dd('System Expired');
        }

        $this->today = date('Y-m-d', strtotime(Carbon::now()));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     * @throws \Exception
     * @throws \Throwable
     */
    public function index(Request $request)
    {
    	$queueToday = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
            ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
    		->select('policlinic_queues.*', 'doctors.doctorName', 'doctors.doctorLastName')
            ->where([
                ['doctor_user.user_id', '=', \Auth::user()['id']]
            ])
            ->whereDate('policlinic_queues.forDate', $this->today)
            ->get();
            
        $cancel_visits = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
            ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
            ->select('policlinic_queues.*', 'doctors.id')
            ->where([
                ['doctor_user.user_id', '=', \Auth::user()['id']],
                ['policlinic_queues.flag', '=', 0]
            ])
            ->whereDate('policlinic_queues.forDate', $this->today)
            ->get();

        if(getdate(strtotime(Carbon::now()))['mday'] <= 28){

            $queueWeek = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
                ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('policlinic_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
                ->where('doctor_user.user_id', '=', \Auth::user()['id'])
                ->whereYear('policlinic_queues.forDate', date('Y', strtotime(Carbon::now())))
                ->whereMonth('policlinic_queues.forDate', date('m', strtotime(Carbon::now())))
                //->whereBetween('policlinic_queues.forDate', [$this->dayRangeWeek(Carbon::now())['start'], $this->dayRangeWeek(Carbon::now())['end']])
                ->whereDay('policlinic_queues.forDate', '>=', $this->dayRangeWeek(Carbon::now())['start'])
                ->orWhere(function ($query) {
                    $query
                    	->where('doctor_user.user_id', '=', \Auth::user()['id'])
                		->whereYear('policlinic_queues.forDate', date('Y', strtotime(Carbon::now())))
                		->whereMonth('policlinic_queues.forDate', date('m', strtotime(Carbon::now())))
                        ->whereDay('policlinic_queues.forDate', '<', $this->dayRangeWeek(Carbon::now())['end']);
                }) 
                // ->whereDay('policlinic_queues.forDate', '<', $this->dayRangeWeek(Carbon::now())['end']) 
                ->get();

        }else{

            $queueWeek = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
                ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('policlinic_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
                ->where('doctor_user.user_id', '=', \Auth::user()['id'])
                ->whereMonth('policlinic_queues.forDate', date('m', strtotime(Carbon::now())))
                ->whereDay('policlinic_queues.forDate', '>=', $this->dayRangeWeek(Carbon::now())['start'])
                ->orWhere(function ($query) {
                    $query
                        ->where('doctor_user.user_id', '=', \Auth::user()['id'])
                        ->whereMonth('policlinic_queues.forDate', date('m', strtotime(Carbon::now() . ' + 1 months')))
                        ->whereDay('policlinic_queues.forDate', '<=', $this->dayRangeWeek(Carbon::now())['end']);
                })    
                ->get();

        }

        $queueAll = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
            ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
            ->select('policlinic_queues.*')
            ->where([
                ['doctor_user.user_id', '=', \Auth::user()['id']]
            ])
            ->latest('id')
            ->paginate(10); 

        if($request->ajax()){

            return response()->json(view('policlinicQueue.queueAllPaginate', compact('queueAll'))->render());

        }      

    	return view('policlinicQueue.index', compact('queueToday', 'queueAll', 'queueWeek', 'cancel_visits'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * @throws \Throwable
     */
    public function searchAll(Request $request)
    {
        if($request->ajax()){

            $this->txtSearchAll = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('search'));

            $queueAll = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
                ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
                ->select('policlinic_queues.*')
                ->where([
                    ['doctor_user.user_id', '=', \Auth::user()['id']],
                    ['policlinic_queues.name', 'like', "%$this->txtSearchAll%"],
                ])
                ->orWhere(function ($query) {
                    $query->where('doctor_user.user_id', '=', \Auth::user()['id'])
                          ->where('policlinic_queues.trackingCode', 'like', "%$this->txtSearchAll%");
                })               
                ->get();

            return response()->json(view('policlinicQueue.searchAll', compact('queueAll'))->render());
        }      
    }



    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        if($request->ajax()){
            $this->cancel['doctor_id']  = $this->doctorID()['id'];
            $this->cancel['start_time']	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->cancel['end_time']   = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $sth = PoliclinicQueue::whereBetween('forDate', [$this->cancel['start_time'], $this->cancel['end_time']])
                ->where('doctor_id', $this->cancel['doctor_id'])
                ->update(['flag' => 0]);

            if (! $sth) {
                return response()->json(['status' => 1]);
            }    

            return response()->json(['status' => 0]);

        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelTime(Request $request)
    {
        if ($request->ajax()){
            
            $this->cancelTime = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('time'), 0, 10)));

            $cancel_visits = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
                ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
                ->select('policlinic_queues.*', 'doctors.doctorName', 'doctors.doctorLastName')
                ->where([
                    ['doctor_user.user_id', '=', \Auth::user()['id']],
                    ['policlinic_queues.flag', '=', 0]
                ])
                ->whereDate('policlinic_queues.forDate', $this->cancelTime)
                ->get();

            return response()->json(view('policlinicQueue.cancelTime', compact('cancel_visits'))->render());
        }
    }


    /**
     * @param $time
     * @return \Illuminate\Http\Response
     */
    public function textFileGenerate($time)
    {
        $date = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($time, 0, 10)));
        $test = null;

        $txt = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
            ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
            ->select('policlinic_queues.*')
            ->where([
                ['doctor_user.user_id', '=', \Auth::user()['id']],
                ['policlinic_queues.flag', '=', 0]
            ])
            ->whereDate('policlinic_queues.forDate', $date)
            ->get();

        if ($txt) {

            foreach ($txt as $key => $value) {
                $test .= $value['cardNumber'] . "," . $value['amount'] . "\r\n";
            }

            $_file = (string) $test;
            $myName = uniqid() . ".txt";
            $headers = ['Content-type'=>'text/plain', 'test'=>'YoYo', 'Content-Disposition'=>sprintf('attachment; filename="%s"', $myName),'X-BooYAH'=>'WorkyWorky','Content-Length'=>sizeof($_file)];
            
            return response()->make($_file, 200, $headers);

        }
    }


    /**
     * @param Request $request
     */
    public function returnAmount(Request $request)
    {
        if ($request->ajax()) {

            $this->returnAmount = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('time'), 0, 10)));

            PoliclinicQueue::where([
                    ['returnAmount', 0],
                    ['flag', 0]
                ])
                ->whereDate('forDate', $this->returnAmount)
                ->update(['returnAmount' => 1]);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activeVisits(Request $request)
    {
        if ($request->ajax()){
            
            $doctor = Doctor::join('doctor_user', 'doctors.id', '=', 'doctor_user.doctor_id')
                ->where('doctor_user.user_id', \Auth::user()['id'])
                ->value('doctor_id');

            $this->activeVisits['start_time'] 	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->activeVisits['end_time'] 	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $sth = PoliclinicQueue::whereBetween('forDate', [$this->activeVisits['start_time'], $this->activeVisits['end_time']])
                ->where('doctor_id', $doctor)
                ->update(['flag' => 1]);

            if (! $sth) {
                return response()->json(['status' => 1]);
            }    

            return response()->json(['status' => 0]);
        }
    }




    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showVisits(Request $request)
    {
        if ($request->ajax()){
            
            $this->showVisits['start_time']	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->showVisits['end_time'] 	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $sth = PoliclinicQueue::join('doctor_user', 'doctor_user.doctor_id', '=', 'policlinic_queues.doctor_id')
	            ->join('doctors', 'policlinic_queues.doctor_id', '=', 'doctors.id')
	            ->whereBetween('forDate', [$this->showVisits['start_time'], $this->showVisits['end_time']])
                ->where([
                	['policlinic_queues.doctor_id', $this->doctorID()['id']],
                	['doctor_user.user_id', '=', \Auth::user()['id']]
                ])
                ->select('policlinic_queues.*')
                ->get();

            if (count($sth) == 0) {
                return response()->json(['status' => 1]);
            }    

            return response()->json(view('policlinicQueue.showVisits', compact('sth'))->render());
        }
    }

}