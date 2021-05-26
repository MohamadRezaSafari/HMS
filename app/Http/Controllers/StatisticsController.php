<?php

namespace App\Http\Controllers;

use App\HCL_User;
use App\HospitalQueue;
use App\User;
use Illuminate\Http\Request;
use App\Doctor;
use App\Expertise;
use App\Http\Requests;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\Session\Session;
use MohammadReza\SecurityBundle;

class StatisticsController extends Controller
{
    /**
     * @var array
     */
    private $ajaxResult = array();

    /**
     * @var array
     */
    private $cancelResult = array();

    /**
     * @var array
     */
    private $chartResult = array();

    /**
     * @var array
     */
    private $excel = array();

    /**
     * @var string
     */
    private $fileDate;


    /**
     * StatisticsController constructor.
     */
	public function __construct()
	{
		$this->middleware(['auth', 'roleHospital']);

        // Cookie System
	}


    /**
     * @return mixed
     */
	private function auth()
    {
        return HCL_User::join('users', 'hcl_user.user_id', '=', 'users.id')
            ->join('healthcare_center_lists', 'hcl_user.hcl_id', '=', 'healthcare_center_lists.id')
            ->where('users.id', \Auth::id())
            ->select('healthcare_center_lists.id')
            ->value('id');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $doctors = Doctor::join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
            ->select('doctors.*', 'expertises.expertiseName')
            ->get();

        $sth = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->whereDate('hospital_queues.forDate', date('Y-m-d', time()))
            ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
            ->where('hospital_queues.hospital_id', $this->auth())
            ->get();

    	return view('statistics.index', compact('sth', 'doctors'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxResult(Request $request)
    {
        if ($request->ajax()){
            
            $this->ajaxResult['start_time'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->ajaxResult['end_time']   = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $doctors = Doctor::join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('doctors.*', 'expertises.expertiseName')
                ->get();

            $sth = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->whereBetween('hospital_queues.forDate', [$this->ajaxResult['start_time'], $this->ajaxResult['end_time']])
                ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
                ->where('hospital_queues.hospital_id', $this->auth())
                ->get();

            return response()->json(view('statistics.ajaxResult', compact('doctors', 'sth'))->render());
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelResult(Request $request)
    {
        if ($request->ajax()){

            $this->cancelResult['start_time'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->cancelResult['end_time']   = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $doctors = Doctor::join('expertises', 'doctors.expertise_id', '=', 'expertises.id')
                ->select('doctors.*', 'expertises.expertiseName')
                ->get();

            $sth = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->whereBetween('hospital_queues.forDate', [$this->cancelResult['start_time'], $this->cancelResult['end_time']])
                ->where('hospital_queues.flag', 0)
                ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
                ->where('hospital_queues.hospital_id', $this->auth())
                ->get();

            return response()->json(view('statistics.cancelResult', compact('doctors', 'sth'))->render());
        }
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function chart()
    {
        $doctors    = null;
        $visit      = null;
        $cancel     = null;

        $doctorsList = Doctor::orderby('id', 'desc')->get();

        foreach ($doctorsList as $value){
            $doctors .=  "'" . $value['doctorLastName'] . "',";
        }

        $v = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->whereDate('hospital_queues.forDate', date('Y-m-d', time()))
            ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
            ->where('hospital_queues.hospital_id', $this->auth())
            ->orderby('doctors.id', 'desc')
            ->get();

        $c = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->whereDate('hospital_queues.forDate', date('Y-m-d', time()))
            ->where('hospital_queues.flag', 0)
            ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
            ->where('hospital_queues.hospital_id', $this->auth())
            ->get();

        foreach($doctorsList as $value){
            $y = 0;
            foreach($c as $key => $item){
                if($item->doctor_id == $value->id)
                    $y+=1;
                if (count($c) == ($key+1))
                    $cancel .=  $y . ",";
            }
        }

        foreach($doctorsList as $value){
            $x = 0;
            foreach($v as $key => $item){
                if($item->doctor_id == $value->id)
                    $x+=1;
                if (count($v) == ($key+1))
                    $visit .=  $x . ",";
            }
        }

        return view('statistics.chart', compact('doctors', 'sth', 'visit', 'cancel'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxChart(Request $request)
    {
        if ($request->ajax()) {

            $this->chartResult['start_time'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_time'), 0, 10)));
            $this->chartResult['end_time']   = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_time'), 0, 10)));

            $doctors    = null;
            $visit      = null;
            $cancel     = null;

            $doctorsList = Doctor::orderby('id', 'desc')->get();

            foreach ($doctorsList as $value){
                $doctors .=  "'" . $value['doctorLastName'] . "',";
            }

            $v = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->whereBetween('hospital_queues.forDate', [$this->chartResult['start_time'], $this->chartResult['end_time']])
                ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
                ->where('hospital_queues.hospital_id', $this->auth())
                ->orderby('doctors.id', 'desc')
                ->get();

            foreach($doctorsList as $value){
                $x = 0;
                foreach($v as $key => $item){
                    if($item->doctor_id == $value->id)
                        $x+=1;
                    if (count($v) == ($key+1))
                        $visit .=  $x . ",";
                }
            }

            foreach($doctorsList as $value){
                $x = 0;
                foreach($v as $key => $item){
                    if($item->doctor_id == $value->id)
                        $x+=1;
                    if (count($v) == ($key+1))
                        $visit .=  $x . ",";
                }
            }

            $c = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
                ->whereBetween('hospital_queues.forDate', [$this->chartResult['start_time'], $this->chartResult['end_time']])
                ->where('hospital_queues.flag', 0)
                ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
                ->where('hospital_queues.hospital_id', $this->auth())
                ->get();

            foreach($doctorsList as $value){
                $y = 0;
                foreach($c as $key => $item){
                    if($item->doctor_id == $value->id)
                        $y+=1;
                    if (count($c) == ($key+1))
                        $cancel .=  $y . ",";
                }
            }

            return response()->json(view('statistics.chartResult', compact('doctors', 'sth', 'visit', 'cancel'))->render());
        }
    }


    /**
     * @param $start
     * @param $end
     * @return mixed
     */
    public function excelDownload($start, $end)
    {
        $this->excel['start_time'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($start, 0, 10)));
        $this->excel['end_time']   = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($end, 0, 10)));

        $doctors    = null;
        $visit      = null;
        $cancel     = null;
        $data       = array();

        $doctorsList = Doctor::orderby('id', 'desc')->get();

        foreach ($doctorsList as $value){
            $doctors .=  $value['doctorLastName'] . ",";
        }

        $v = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->whereBetween('hospital_queues.forDate', [$this->excel['start_time'], $this->excel['end_time']])
            ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
            ->where('hospital_queues.hospital_id', $this->auth())
            ->orderby('doctors.id', 'desc')
            ->get();

        foreach($doctorsList as $value){
            $x = 0;
            foreach($v as $key => $item){
                if($item->doctor_id == $value->id)
                    $x+=1;
                if (count($v) == ($key+1))
                    $visit .=  $x . ",";
            }
        }

        $c = HospitalQueue::join('doctors', 'hospital_queues.doctor_id', '=', 'doctors.id')
            ->whereBetween('hospital_queues.forDate', [$this->excel['start_time'], $this->excel['end_time']])
            ->where('hospital_queues.flag', 0)
            ->select('doctors.doctorName', 'doctors.doctorLastName', 'hospital_queues.doctor_id')
            ->where('hospital_queues.hospital_id', $this->auth())
            ->orderby('doctors.id', 'desc')
            ->get();

        foreach($doctorsList as $value){
            $y = 0;
            foreach($c as $key => $item){
                if($item->doctor_id == $value->id)
                    $y+=1;
                if (count($c) == ($key+1))
                    $cancel .=  $y . ",";
            }
        }

        $dd = array_filter(explode(',', $doctors));
        $cc = $cancel ? array_diff(explode(',', $cancel), array("")) : 0;
        $vv = array_diff(explode(',', $visit), array(""));

        foreach ($vv as $key => $value) {
            $data[$key] = [$dd[$key], $vv[$key], $cc[$key]];
        }

        $this->fileDate = jdate('Y-n-j',
            mktime(
                date('H', strtotime($this->excel['start_time'])),
                date('i', strtotime($this->excel['start_time'])),
                date('s', strtotime($this->excel['start_time'])),
                date('m', strtotime($this->excel['start_time'])),
                date('d', strtotime($this->excel['start_time'])),
                date('Y', strtotime($this->excel['start_time']))
        ))
        . " , " .
        jdate('Y-n-j',
            mktime(
                date('H', strtotime($this->excel['end_time'])),
                date('i', strtotime($this->excel['end_time'])),
                date('s', strtotime($this->excel['end_time'])),
                date('m', strtotime($this->excel['end_time'])),
                date('d', strtotime($this->excel['end_time'])),
                date('Y', strtotime($this->excel['end_time']))
        ))
        ;

        return \Excel::create(uniqid(), function($excel) use ($data) {
            $excel->sheet($this->fileDate, function($sheet) use ($data)
            {
                $sheet->fromArray($data);
                $sheet->row(1, array(
                     'پزشک', 'تعداد ویزیت', 'لغو ویزیت'
                ));
                $sheet->row(1, function($row) {
                    $row->setBackground('#47e2ed');
                });
            });
        })->download('xls');
    }

}
