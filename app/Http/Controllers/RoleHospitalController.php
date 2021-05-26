<?php

namespace App\Http\Controllers;

use App\Day;
use App\Day_Timing;
use App\Doctor;
use App\HCL_User;
use App\HealthcareCenterList;
use App\Ticket;
use App\TicketAnswer;
use App\Timing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use MohammadReza\SecurityBundle;
use MohammadReza\Laravel;
use App\Http\Requests;
use App\Expertise;
use App\City;
use App\State;
use App\City_Expertise;
use App\Doctor_User;
use App\User;
use App\Role_User;

class RoleHospitalController extends Controller
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var array
     */
    private $data_doctor = array();


    /**
     * @var integer
     */
    private $doctorId_for_edit;


    /**
     * @var integer
     */
    private $doctorId_for_update;

    /**
     * @var integer
     */
    private $doctorId_for_destroy;


    /**
     * @var integer
     */
    private $doctorId_for_show;


    /**
     * @var integer
     */
    private $doctorId_for_showTime;

    /**
     * @var array
     */
    private $data_time = array();

    /**
     * @var integer
     */
    private $editId;

    /**
     * @var integer
     */
    private $updateId;

    /**
     * @var array
     */
    private $data_update = array();

    /**
     * @var integer
     */
    private $showId;

    /**
     * @var integer
     */
    private $deleteId;

    /**
     * @var integer
     */
    protected $trust;

    /**
     * @var integer
     */
    private $healthcareCenterListId;

    /**
     * @var array
     */
    private $data_ticket = array();


    /**
     * @var integer
     */
    private $ticket_flag_read;


    /**
     * @var string
     */
    private $day_or_wm;

    /**
     * @var array
     */
    private $changePassword = array();

    /**
     * @var array
     */
    private $addUser = array();


    /**
     * RoleHospitalController constructor.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'roleHospital']);

        if (\Cookie::get('a5e0a7e4550411d6')) {
            dd('System Expired');
        }
    }


    /**
     * @return int
     */
    protected function _trust()
    {
        $user = \Auth::user();
        $this->trust = HealthcareCenterList::join('hcl_user', 'healthcare_center_lists.id', '=', 'hcl_user.hcl_id')
            ->join('cities', 'healthcare_center_lists.city_id', '=', 'cities.id')
            ->where('hcl_user.user_id', '=', $user['id'])
            ->select('healthcare_center_lists.trust')
            ->value('trust');
        return $this->trust;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = \Auth::user();

        $hospital = HealthcareCenterList::join('hcl_user', 'healthcare_center_lists.id', '=', 'hcl_user.hcl_id')
            ->join('cities', 'healthcare_center_lists.city_id', '=', 'cities.id')
            ->where('hcl_user.user_id', '=', $user['id'])
            ->select('healthcare_center_lists.*', 'cities.cityName')
            ->get();

        /*$hospitalImages = HealthcareCenterList::join('hcl_user', 'healthcare_center_lists.id', '=', 'hcl_user.hcl_id')
            ->join('galleries', 'healthcare_center_lists.id', '=', 'galleries.healthcareCenterList_id')
            ->where('hcl_user.user_id', '=', $user['id'])
            ->select('healthcare_center_lists.img', 'galleries.img')
            ->get();  */  

        $id = HealthcareCenterList::join('hcl_user', 'healthcare_center_lists.id', '=', 'hcl_user.hcl_id')
            ->where('hcl_user.user_id', '=', $user['id'])
            ->pluck('healthcare_center_lists.id')
            ->first();

        $doctor = HealthcareCenterList::join('hcl_user', 'healthcare_center_lists.id', '=', 'hcl_user.hcl_id')
            ->join('doctors', 'doctors.healthcareCenterList_id', '=', 'healthcare_center_lists.id')
            ->join('expertises', 'doctors.expertise_id', 'expertises.id')
            ->where('hcl_user.user_id', '=', $user['id'])
            ->select('doctors.*', 'expertises.expertiseName')
            ->paginate(10);

        $time = HealthcareCenterList::join('hcl_user', 'healthcare_center_lists.id', '=', 'hcl_user.hcl_id')
            ->join('doctors', 'healthcare_center_lists.id', 'doctors.healthcareCenterList_id')
            ->join('timings', 'doctors.id', '=', 'timings.doctor_id')
            ->where([
                ['hcl_user.user_id', '=', $user['id']],
                ['timings.for', '=', 'hospital']
            ])
            ->select('timings.*', 'doctors.doctorName', 'doctors.doctorLastName')
            ->get();

        $day = HCL_User::join('healthcare_center_lists', 'hcl_user.hcl_id', '=', 'healthcare_center_lists.id')
            ->join('doctors', 'healthcare_center_lists.id', '=', 'doctors.healthcareCenterList_id')
            ->join('timings', 'doctors.id', '=', 'timings.doctor_id')
            ->join('day_timing', 'day_timing.timing_id', '=', 'timings.id')
            ->join('days', 'day_timing.day_id', '=', 'days.id')
            ->select('days.dayName', 'day_timing.timing_id')
            ->where([
                ['hcl_user.user_id', '=', $user['id']],
                ['timings.for', '=', 'hospital']
            ])
            ->get();

        $ticket = Ticket::join('ticket_answers', 'tickets.id', '=', 'ticket_answers.ticket_id')
            ->select('ticket_answers.*', 'tickets.subject', 'tickets.message')
            ->where([
                ['tickets.user_id', '=', $user['id']],
                //['read', '=', 0]
            ])
            ->latest('id')
            ->get();

        $ticket_count = Ticket::join('ticket_answers', 'tickets.id', '=', 'ticket_answers.ticket_id')
            ->select('ticket_answers.*', 'tickets.subject')
            ->where([
                ['tickets.user_id', '=', $user['id']],
                ['read', '=', 0]
            ])
            ->count();

        if($request->ajax()){
            // if (count($doctor) == 0) {
            //     return response()->json(['data' => 0]);
            // }
            // return response()->json(view('hclUser.p', compact('hospital', 'doctor'))->render());
            return response()->json(view('hclUser.doctorPaginate', compact('hospital', 'doctor'))->render());

        }

        return view('hclUser.index', compact('hospital', 'doctor', 'id', 'time', 'ticket', 'ticket_count', 'day'));
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function doctor($id)
    {
        $this->id = (new SecurityBundle\SBInput())->getNumberInt($id);
        $expertise = Expertise::pluck('expertiseName', 'id');
        $list = HealthcareCenterList::where('id', '=', $this->id)->get();

        return view('hclUser.doctor', compact('expertise', 'list'));
    }


    /**
     * @param Requests\DoctorRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function addDoctor(Requests\DoctorRequest $request)
    {
        $this->data_doctor['doctorName'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('doctorName'));
        $this->data_doctor['doctorLastName'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('doctorLastName'));
        @$this->data_doctor['academicRank'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('academicRank'));
        $this->data_doctor['doctorTime'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('doctorTime'));
        @$this->data_doctor['fellowship'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('fellowship'));
        @$this->data_doctor['graduateFrom'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('graduateFrom'));
        $this->data_doctor['expertiseField'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('expertiseField'));
        @$this->data_doctor['specialty'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('specialty'));
        @$this->data_doctor['clinicName'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('clinicName'));
        @$this->data_doctor['clinicTell'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('clinicTell'));
        @$this->data_doctor['clinicAddress'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('clinicAddress'));
        $this->data_doctor['expertise_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('expertise_id'));
        $this->data_doctor['clinicStatus'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('clinicStatus'));
        $this->data_doctor['healthcareCenterList_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('healthcareCenterList_id'));
        $this->data_doctor['state_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('state_id'));
        $this->data_doctor['city_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('city_id'));
        $this->data_doctor['hospitalVisitPrice'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('hospitalVisitPrice'));
        $this->data_doctor['doctorOnlineVisitStatus'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('doctorOnlineVisitStatus'));
        $this->data_doctor['doctorAddress'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('doctorAddress'));

        if($this->_trust() == 0){
            $this->data_doctor['confirmTitle'] = "افزودن دکتر جدید";
        }else if ($this->_trust() == 1){
            $this->data_doctor['confirm'] = 1;
        }

        if($request->hasFile('doctorImg')){
            $this->data_doctor['doctorImg'] = (new Laravel\LaravelUpload())->upload($request, 'jpeg|jpg|png', '/public/img/doctors/', 'doctorImg');
        }

        $check = City_Expertise::where([
            ['city_id', '=', $this->data_doctor['city_id']],
            ['expertise_id', '=', $this->data_doctor['expertise_id']]
        ])->count();

        if($check == 0){
            if(isset($this->data_doctor['city_id']) && isset($this->data_doctor['expertise_id'])){
                City_Expertise::insert([
                    ['city_id' => $this->data_doctor['city_id'], 'expertise_id' => $this->data_doctor['expertise_id']]
                ]);
            }
        }

        Doctor::create($this->data_doctor);
        Session::flash('success_msg', 'پزشک مورد نظر با موفقیت ثبت گردید');
        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $this->doctorId_for_edit = (new SecurityBundle\SBInput())->getNumberInt($id);

        $doctor = Doctor::findOrFail($this->doctorId_for_edit);
        $city = City::pluck('cityName', 'id');
        $state = State::pluck('stateName', 'id');
        $expertise = Expertise::pluck('expertiseName', 'id');
        $list = HealthcareCenterList::pluck('healthcareCenterListName', 'id');

        return view('hclUser.edit', compact('doctor', 'city', 'state', 'expertise', 'list'));
    }


    /**
     * @param Requests\DoctorRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Requests\DoctorRequest $request, $id)
    {
        $this->doctorId_for_update = (new SecurityBundle\SBInput())->getNumberInt($id);
        $doctor = Doctor::findOrFail($this->doctorId_for_update);
        $img = Doctor::where('id', '=', $this->doctorId_for_update)->first();

        $this->data_doctor['doctorName'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('doctorName'));
        $this->data_doctor['doctorLastName'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('doctorLastName'));
        @$this->data_doctor['academicRank'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('academicRank'));
        $this->data_doctor['doctorTime'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('doctorTime'));
        @$this->data_doctor['fellowship'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('fellowship'));
        @$this->data_doctor['graduateFrom'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('graduateFrom'));
        $this->data_doctor['expertiseField'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('expertiseField'));
        @$this->data_doctor['specialty'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('specialty'));
        @$this->data_doctor['clinicName'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('clinicName'));
        @$this->data_doctor['clinicTell'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('clinicTell'));
        @$this->data_doctor['clinicAddress'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('clinicAddress'));
        $this->data_doctor['expertise_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('expertise_id'));
        $this->data_doctor['clinicStatus'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('clinicStatus'));
        $this->data_doctor['healthcareCenterList_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('healthcareCenterList_id'));
        $this->data_doctor['state_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('state_id'));
        $this->data_doctor['city_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('city_id'));
        $this->data_doctor['hospitalVisitPrice'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('hospitalVisitPrice'));
        $this->data_doctor['doctorOnlineVisitStatus'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('doctorOnlineVisitStatus'));
        $this->data_doctor['doctorAddress'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('doctorAddress'));

        if($this->_trust() == 0){
            $this->data_doctor['confirm'] = 0;
            $this->data_doctor['confirmTitle'] = "ویرایش دکتر";
        }else if ($this->_trust() == 1){
            $this->data_doctor['confirm'] = 1;
        }

        if($request->hasFile('doctorImg')){
            @unlink(base_path() . '/public/img/doctors/' . $img['doctorImg']);
            $this->data_doctor['doctorImg'] = (new Laravel\LaravelUpload())->upload($request, 'jpeg|jpg|png', '/public/img/doctors/', 'doctorImg');
            $doctor->update($this->data_doctor);
        }else{
            $this->data_doctor['doctorImg'] = $img['doctorImg'];
            $doctor->update($this->data_doctor);
        }
        Session::flash('success_msg', 'اطلاعات پزشک مورد نظر ویرایش گردید.');

        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $this->doctorId_for_show = (new SecurityBundle\SBInput())->getNumberInt($id);
        $doctor = Doctor::findOrFail($this->doctorId_for_show);

        return view('hclUser.show', compact('doctor'));
    }


    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $this->doctorId_for_destroy = (new SecurityBundle\SBInput())->getNumberInt($id);
        if($this->_trust() == 0){
            Doctor::where('id', $this->doctorId_for_destroy)
                ->update([
                    'confirm' => 0,
                    'confirmTitle' => "حذف دکتر"
                ]);
        }else if ($this->_trust() == 1){
            $doctor = Doctor::findOrFail($this->doctorId_for_destroy);
            $img = Doctor::where('id', '=', $this->doctorId_for_destroy)->first();
            $doctor->delete();
            @unlink(base_path() . '/public/img/doctors/' . $img['doctorImg']);
            Timing::where([
                ['doctor_id', '=', $this->doctorId_for_destroy],
                ['for', '=', 'hospital']
            ])->delete();
            $time = Timing::where([
                ['doctor_id', '=', $this->doctorId_for_destroy],
                ['for', '=', 'hospital']
            ])->pluck('id');
            foreach ($time as $item){
                Day_Timing::where('timing_id', '=', $item)->delete();
            }
        }
        Session::flash('success_msg', 'پزشک مورد نظر حذف گردید.');
        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function time($id)
    {
        $this->doctorId_for_showTime = (new SecurityBundle\SBInput())->getNumberInt($id);
        $doctor = Doctor::where('id', '=', $this->doctorId_for_showTime)->get();
        $days = Day::pluck('dayName', 'id');
        return view('hclUser.time', compact('doctor', 'days'));
    }


    /**
     * @param Requests\TimingRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function addTime(Requests\TimingRequest $request)
    {
        $this->day_or_wm = (new SecurityBundle\SBInput())->getString($request->get('day'));

        if($this->day_or_wm == "wm"){
            $this->data_time['end_date'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_date'), 0, 10)));
        }

        if($this->day_or_wm == "day"){
            $this->data_time['end_date'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_date'), 0, 10)));
        }

        $this->data_time['visitCount'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('visitCount'));
        $this->data_time['start_time'] = (new SecurityBundle\SBInput())->getDate($request->get('start_time'));
        $this->data_time['end_time'] = (new SecurityBundle\SBInput())->getDate($request->get('end_time'));
        $this->data_time['start_date'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_date'), 0, 10)));
        $this->data_time['doctor_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('doctor_id'));
        $this->data_time['for'] = (new SecurityBundle\SBInput())->getSpecialChars($request->get('for'));

        if($this->_trust() == 0){
            $this->data_time['confirm'] = 0;
            $this->data_time['confirmTitle'] = "افزودن تایم جدید برای دکتر";
        }elseif ($this->_trust() == 1){
            $this->data_time['confirm'] = 1;
        }

        $time = Timing::create($this->data_time);
        $time->days()->attach($request->get('days'));
        Session::flash('success_msg', 'زمان بندی شما ثبت گردید.');
        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editTime($id)
    {
        $this->editId = (new SecurityBundle\SBInput())->getNumberInt($id);
        $time = Timing::findOrFail($this->editId);
        $days = Day::pluck('dayName', 'id');
        return view('hclUser.timeEdit', compact('time', 'daylist', 'days'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateTime(Request $request)
    {
        $this->updateId = (new SecurityBundle\SBInput())->getNumberInt($request->get('time_id'));
        $time = Timing::findOrFail($this->updateId);

        $this->day_or_wm = (new SecurityBundle\SBInput())->getString($request->get('day'));

        if($this->day_or_wm == "wm"){
            $this->data_update['end_date'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_date'), 0, 10)));
        }

        if($this->day_or_wm == "day"){
            $this->data_update['end_date'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_date'), 0, 10)));
        }

        $this->data_update['visitCount'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('visitCount'));
        $this->data_update['start_time'] = (new SecurityBundle\SBInput())->getDate($request->get('start_time'));
        $this->data_update['end_time'] = (new SecurityBundle\SBInput())->getDate($request->get('end_time'));
        $this->data_update['start_date'] = date("Y-m-d", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('start_date'), 0, 10)));
        //$this->data_update['end_date'] = date("Y-m-d H:i:s", (new SecurityBundle\SBInput())->getDate(mb_substr($request->get('end_date'), 0, 10)));
        //$this->data_update['doctor_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('doctor_id'));
        $this->data_update['for'] = 'hospital';

        if($this->_trust() == 0){
            $this->data_update['confirm'] = 0;
            $this->data_update['confirmTitle'] = "ویرایش زمان بندی دکتر";
        }elseif ($this->_trust() == 1){
            $this->data_update['confirm'] = 1;
        }

        $time->update($this->data_update);
        $time->days()->sync($request->get('day_list'));
        Session::flash('success_msg', 'زمان بندی مورد نظر شما بروز رسانی شد.');
        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTime($id)
    {
        $this->showId = (new SecurityBundle\SBInput())->getNumberInt($id);
        $time = Timing::findORFail($this->showId);
        return view('hclUser.timeShow', compact('time'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroyTime(Request $request)
    {
        $this->deleteId = (new SecurityBundle\SBInput())->getNumberInt($request->get('time_id'));
        if($this->_trust() == 0){
            Timing::where('id', $this->deleteId)
                ->update([
                    'confirm' => 0,
                    'confirmTitle' => "حذف زمان بندی دکتر"
                ]);
        }elseif ($this->_trust() == 1){
            $time = Timing::findOrFail($this->deleteId);
            $time->delete();
            $time->days()->detach();
        }
        Session::flash('success_msg', 'زمان بندی مورد نظر حذف گردید.');
        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ticket($id)
    {
        $this->healthcareCenterListId = (new SecurityBundle\SBInput())->getNumberInt($id);
        $hclId = $this->healthcareCenterListId;
        $user_id = \Auth::user()['id'];

        return view('hclUser.ticket', compact('hclId', 'user_id'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function sendTicket(Request $request)
    {
        $this->data_ticket['subject'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('subject'));
        $this->data_ticket['message'] = (new SecurityBundle\SBInput())->getPersianCharacters($request->get('message'));
        $this->data_ticket['user_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('user_id'));
        $this->data_ticket['healthcareCenterList_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('healthcareCenterList_id'));
        $this->data_ticket['ip'] = $request->getClientIp();
        Ticket::create($this->data_ticket);
        Session::flash('success_msg', 'در اسرع وقت نسبت به درخواست شما پاسخ داده خواهد شد.');
        return redirect('roleHospital');
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function readTicket(Request $request)
    {
        if($request->ajax()){
            $this->ticket_flag_read = (new SecurityBundle\SBInput())->getNumberInt($request->get('id'));
            TicketAnswer::where('id', $this->ticket_flag_read)
                ->update(['read' => 1]);
            return response()->json(['status' => 1]);
        }
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function changePassword($id)
    {
        $suggestion = rtrim(strtr(base64_encode(random_bytes(10)), '+/', '-_'), '=');

        return view('hclUser.changePassword', compact('id', 'suggestion'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function changePasswordAction(Request $request)
    {
        $security = (new SecurityBundle\SBInput());

        $this->changePassword['hcl_id'] = $security->getNumberInt($request->get('hcl_id'));
        $this->changePassword['password'] = bcrypt($request->get('password'));

        HCL_User::join('users', 'hcl_user.user_id', '=', 'users.id')
            ->where([
                ['hcl_user.hcl_id', '=', $this->changePassword['hcl_id']],
                ['users.id', '=', \Auth::user()['id']]
            ])
            ->update(['users.password' => $this->changePassword['password']]);

        Session::flash('success_msg', 'رمز عبور با موفقیت به روز رسانی شد.');
        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addUser($id)
    {
        $_user 	= Doctor_User::pluck('doctor_id');
        $doctor = Doctor::whereNotIn('id', $_user)->pluck('doctorLastName', 'id');

        return view('hclUser.addUser', compact('id', 'doctor'));
    }


    /**
     * @param Requests\AddUserRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function addUserAction(Requests\AddUserRequest $request)
    {
        $this->addUser['email'] = (new SecurityBundle\SBInput())->getEmail($request->get('email'));
        $this->addUser['doctor_id'] = (new SecurityBundle\SBInput())->getNumberInt($request->get('doctor_id'));
        $this->addUser['password'] = bcrypt($request->get('password'));
        $this->addUser['name'] = 'doctor' . $this->addUser['doctor_id'];

        User::create($this->addUser);

        $id = User::latest('id')->take(1)->value('id');

        Doctor_User::insert([
            'doctor_id' => $this->addUser['doctor_id'],
            'user_id' => $id
        ]);

        Role_User::insert([
            'role_id' => 5,
            'user_id' => $id
        ]);

        Session::flash('success_msg', 'کاربر مورد نظر ثبت گردید.');
        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function setting($id)
    {
        $setting = HealthcareCenterList::findOrFail(intval($id));
        $city = City::pluck('cityName', 'id');
        $state = State::pluck('stateName', 'id');
        $h = \App\HealthcareCenter::pluck('healthcareCenterName', 'id');

        return view('hclUser.setting', compact('setting', 'h', 'state', 'city'));
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function settingUpdate(Request $request, $id)
    {
        $sth = $request->all();
        $list = HealthcareCenterList::findOrFail($id);
        $img = HealthcareCenterList::where('id', '=', $id)->first();

        if($request->hasFile('img')){
            @unlink(base_path() . '/public/img/healthcareCenterList/' . $img['img']);
            $sth['img'] = (new Laravel\LaravelUpload())->upload($request, 'jpeg|jpg|png', '/public/img/healthcareCenterList/', 'img');
            $list->update($sth);
        }else{
            $sth['img'] = $img['img'];
            $list->update($sth);
        }

        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function pic($id)
    {
        $pic = \App\Gallery::where('healthcareCenterList_id', '=', intval($id))->get();

        return view('hclUser.pic', compact('pic', 'id'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function picUpload(Request $request)
    {
        $id = $request->get('healthcareCenterList_id');
        $sth = $request->all();

        if($request->hasFile('img')){
            $sth['img'] = (new Laravel\LaravelUpload())->upload($request, 'jpeg|jpg|png', '/public/img/healthcareCenterList/', 'img');
        }

        \App\Gallery::create($sth);

        return redirect('roleHospital');
    }


    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function picDelete($id)
    {
        $gallery = \App\Gallery::where('id', '=', $id)->first();

        @unlink(base_path() . '/public/img/healthcareCenterList/' . $gallery['img']);

        $gallery->delete();

        return redirect('roleHospital');
    }

}