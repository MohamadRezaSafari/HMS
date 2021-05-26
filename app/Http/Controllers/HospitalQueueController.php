<?php

namespace App\Http\Controllers;

use App\HospitalSms;
use App\SmsPropertyHealthCareCenterList;
use Colors\RandomColor;
use Illuminate\Http\Request;
use MohammadReza\SecurityBundle;
use App\Http\Requests;
use App\HospitalQueue;
use App\Hcl_User;
use Carbon\Carbon;
use App\Doctor;
use App\Expertise;
use App\HealthcareCenterList;

class HospitalQueueController extends Controller
{

    /**
     * @var int
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
     * @var array
     */
    private $activeVisits = array();

    /**
     * @var array
     */
    private $showVisits = array();

    /**
     * @var string
     */
    private $history;

    /**
     * @var array
     */
    private $sms = array();



    /**
     * @param $date
     * @return mixed
     */
    protected function _month($date)
    {
        return getdate($date)['mon'];
    }

    /**
     * @param $day
     * @return array
     */
    protected function dayRangeWeek($day)
    {
        $this->range = getdate(strtotime($day))['mday'];

        if($this->range <= 7){

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

        else{

            $this->dayRange['start'] = 29;
            $this->dayRange['end'] = 6;

        }

        return $this->dayRange;
    }


    /**
     * RoleHospitalTimeController constructor.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'roleHospital']);

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
//         $rand = dechex(rand(0x000000, 0xFFFFFF));
//         $color = substr(md5(rand()), 0, 6);
// echo('#' . $color);
// die();
        $doctors = HealthcareCenterList::join('hcl_user', 'hcl_user.hcl_id', '=', 'healthcare_center_lists.id')
            ->join('doctors', 'doctors.healthcareCenterList_id', '=', 'healthcare_center_lists.id')
            ->where('hcl_user.user_id', '=', \Auth::user()['id'])
            ->pluck('doctors.doctorLastName', 'doctors.id');

        $cancel_visits = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
            ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
            ->select('hospital_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
            ->where([
                ['hcl_user.user_id', '=', \Auth::user()['id']],
                ['hospital_queues.flag', '=', 0]
            ])
            ->whereDate('hospital_queues.forDate', $this->today)
            ->get();

        $queueToday = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
            ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
            ->select('hospital_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
            ->where([
                ['hcl_user.user_id', '=', \Auth::user()['id']]
            ])
            ->whereDate('hospital_queues.forDate', $this->today)
            ->get();

        if(getdate(strtotime(Carbon::now()))['mday'] <= 28){

            $queueWeek = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
                ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('hospital_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
                ->where('hcl_user.user_id', '=', \Auth::user()['id'])
                ->whereYear('hospital_queues.forDate', date('Y', strtotime(Carbon::now())))
                ->whereMonth('hospital_queues.forDate', date('m', strtotime(Carbon::now())))
                ->whereDay('hospital_queues.forDate', '>=', $this->dayRangeWeek(Carbon::now())['start'])
                ->whereDay('hospital_queues.forDate', '<', $this->dayRangeWeek(Carbon::now())['end'])
                ->orderby('hospital_queues.forDate', 'asc')
                ->get();

        }else{

            $queueWeek = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
                ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('hospital_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
                ->where('hcl_user.user_id', '=', \Auth::user()['id'])
                ->whereMonth('hospital_queues.forDate', date('m', strtotime(Carbon::now())))
                ->whereDay('hospital_queues.forDate', '>=', $this->dayRangeWeek(Carbon::now())['start'])
                ->orWhere(function ($query) {
                    $query
                        ->where('hcl_user.user_id', '=', \Auth::user()['id'])
                        ->whereMonth('hospital_queues.forDate', date('m', strtotime(Carbon::now() . ' + 1 months')))
                        ->whereDay('hospital_queues.forDate', '<=', $this->dayRangeWeek(Carbon::now())['end']);
                })
                ->orderby('hospital_queues.forDate', 'asc')
                ->get();

        }

        $queueAll = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
            ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
            ->select('hospital_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
            ->where([
                ['hcl_user.user_id', '=', \Auth::user()['id']]
            ])
            ->latest('id')
            ->paginate(10);

        if($request->ajax()){

            return response()->json(view('hospitalQueue.queueAllPaginate', compact('queueAll'))->render());

        }

        return view('hospitalQueue.index', compact('queueToday', 'queueAll', 'queueWeek', 'doctors', 'cancel_visits'));
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

            $queueAll = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
                ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('hospital_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
                ->where([
                    ['hcl_user.user_id', '=', \Auth::user()['id']],
                    ['hospital_queues.name', 'like', "%$this->txtSearchAll%"],
                ])
                ->orWhere(function ($query) {
                    $query->where('hcl_user.user_id', '=', \Auth::user()['id'])
                        ->where('hospital_queues.trackingCode', 'like', "%$this->txtSearchAll%");
                })
                ->get();

            return response()->json(view('hospitalQueue.searchAll', compact('queueAll'))->render());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        if($request->ajax()){

            $this->cancel['doctor_id'] 	= (new SecurityBundle\SBInput())->getNumberInt($request->get('doctorId'));
            $this->cancel['start_time'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->cancel['end_time'] 	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $union = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
                ->whereBetween('forDate', [$this->cancel['start_time'], $this->cancel['end_time']])
                ->where([
                    ['doctor_id', $this->cancel['doctor_id']],
                    ['hcl_user.user_id', '=', \Auth::user()['id']]
                ]);

            $union->update(['flag' => 0, 'sms_status' => 1]);
            $numbers = $union->pluck('mobile');
            $doctor = $union->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')->pluck('forDate', 'doctorLastName');
            $mobiles = null;

            $hospital = $union
                ->join('healthcare_center_lists', 'hospital_queues.hospital_id', '=', 'healthcare_center_lists.id')
                ->select('hospital_queues.*', 'healthcare_center_lists.healthcareCenterListName')
                ->value('healthcareCenterListName');

            foreach ($doctor as $doctor => $date){
                $sms_date = jdate('Y/n/j', mktime(
                    date('H', strtotime($date)),
                    date('i', strtotime($date)),
                    date('s', strtotime($date)),
                    date('m', strtotime($date)),
                    date('d', strtotime($date)),
                    date('Y', strtotime($date))));
                $sms_doctorLastName = $doctor;
            }

            foreach ($numbers as $item){
                $mobiles .= $item . ",";
            }

            $_auth = $this->hospitalAuthUser();
            $msg = SmsPropertyHealthCareCenterList::where([
                    ['smsProperty_id', 3],
                    ['status', 1],
                    ['healthcareCenterList_id', $_auth]
                ])->value('sms_message');
            $smsResult = $this->sendSMS([$numbers], $msg ."-". $hospital  . "- دکتر " . $sms_doctorLastName ." - ". $sms_date);
            $this->sms['hospital_id'] = $_auth;
            $this->sms['mobiles'] = $mobiles;
            $this->sms['rec_id'] = $smsResult['recId'];
            $this->sms['delivery_status'] = $smsResult['delivery'];
            $this->sms['created_at'] = Carbon::now();

            HospitalSms::create($this->sms);

            if (! $union->get()) {
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

            $cancel_visits = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
                ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('hospital_queues.*', 'doctors.doctorName', 'doctors.doctorLastName', 'expertises.expertiseName')
                ->where([
                    ['hcl_user.user_id', '=', \Auth::user()['id']],
                    ['hospital_queues.flag', '=', 0]
                ])
                ->whereDate('hospital_queues.forDate', $this->cancelTime)
                ->get();

            return response()->json(view('hospitalQueue.cancelTime', compact('cancel_visits'))->render());
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

        $txt = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
            ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->select('hospital_queues.*', 'doctors.doctorName', 'doctors.doctorLastName')
            ->where([
                ['hcl_user.user_id', '=', \Auth::user()['id']],
                ['hospital_queues.flag', '=', 0]
            ])
            ->whereDate('hospital_queues.forDate', $date)
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

            HospitalQueue::where([
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
            
            $this->activeVisits['doctor_id'] 	= (new SecurityBundle\SBInput())->getNumberInt($request->get('doctorId'));
            $this->activeVisits['start_time'] 	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->activeVisits['end_time'] 	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $union = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
                ->whereBetween('forDate', [$this->activeVisits['start_time'], $this->activeVisits['end_time']])
                ->where([
                    ['doctor_id', $this->activeVisits['doctor_id']],
                    ['hcl_user.user_id', '=', \Auth::user()['id']]
                ]);

            $union->update(['flag' => 1, 'sms_status' => 1]);
            $numbers = $union->pluck('mobile');
            $doctor = $union->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')->pluck('forDate', 'doctorLastName');
            $mobiles = null;

            $hospital = $union
                ->join('healthcare_center_lists', 'hospital_queues.hospital_id', '=', 'healthcare_center_lists.id')
                ->select('hospital_queues.*', 'healthcare_center_lists.healthcareCenterListName')
                ->value('healthcareCenterListName');

            foreach ($doctor as $doctor => $date){
                $sms_date = jdate('Y/n/j', mktime(
                    date('H', strtotime($date)),
                    date('i', strtotime($date)),
                    date('s', strtotime($date)),
                    date('m', strtotime($date)),
                    date('d', strtotime($date)),
                    date('Y', strtotime($date))));
                $sms_doctorLastName = $doctor;
            }

            foreach ($numbers as $item){
                $mobiles .= $item . ",";
            }

            $_auth = $this->hospitalAuthUser();
            $msg = SmsPropertyHealthCareCenterList::where([
                ['smsProperty_id', 4],
                ['status', 1],
                ['healthcareCenterList_id', $_auth]
            ])->value('sms_message');
            $smsResult = $this->sendSMS([$numbers], $msg ."-". $hospital  . "- دکتر " . $sms_doctorLastName ." - ". $sms_date);
            $this->sms['hospital_id'] = $_auth;
            $this->sms['mobiles'] = $mobiles;
            $this->sms['rec_id'] = $smsResult['recId'];
            $this->sms['delivery_status'] = $smsResult['delivery'];
            $this->sms['created_at'] = Carbon::now();

            HospitalSms::create($this->sms);

            if (! $union->get()) {
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
            
            $this->showVisits['doctor_id'] 	= (new SecurityBundle\SBInput())->getNumberInt($request->get('doctorId'));
            $this->showVisits['start_time']	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->showVisits['end_time'] 	= date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $sth = HospitalQueue::join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
	            ->join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
	            ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
	            ->whereBetween('forDate', [$this->showVisits['start_time'], $this->showVisits['end_time']])
                ->where([
                	['doctor_id', $this->showVisits['doctor_id']],
                	['hcl_user.user_id', '=', \Auth::user()['id']]
                ])
                ->select('hospital_queues.*', 'expertises.expertiseName', 'doctors.doctorName', 'doctors.doctorLastName')
                ->get();

            if (count($sth) == 0) {
                return response()->json(['status' => 1]);
            }    

            return response()->json(view('hospitalQueue.showVisits', compact('sth'))->render());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        if ($request->ajax()) {
            
            $this->history = (new SecurityBundle\SBInput())->getNumberInt($request->get('history'));

            $history = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->join('hcl_user', 'hcl_user.hcl_id', '=', 'hospital_queues.hospital_id')
                ->select('hospital_queues.*', 'doctors.doctorLastName', 'doctors.doctorName', 'expertises.expertiseName')
                ->where([
                    ['hospital_queues.nationalCode', $this->history],
                    ['hcl_user.user_id', '=', \Auth::user()['id']]
                ])
                ->get();

            if (count($history) == 0) {
                return response()->json(['status' => 1]);
            }    

            return response()->json(view('hospitalQueue.history', compact('history'))->render());
        }
    }


}