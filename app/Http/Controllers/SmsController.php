<?php

namespace App\Http\Controllers;

use App\HospitalSms;
use App\SmsPropertyHealthCareCenterList;
use App\SmsSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use MohammadReza\SecurityBundle;
use App\Http\Requests;
use App\HospitalQueue;
use App\HCL_User;


class SmsController extends Controller
{
    /**
     * @var array
     */
    private $sms = array();



    /**
     * SmsController constructor.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'roleHospital']);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function txt()
    {
        $txt = SmsPropertyHealthCareCenterList::join('sms_properties', 'smsproperty_healthcarecenterlist.smsProperty_id', '=', 'sms_properties.id')
            ->select('smsproperty_healthcarecenterlist.*', 'sms_properties.property_name')
            ->get();

        return view('sms.txt', compact('txt'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editTxt($id)
    {
        $txt = SmsPropertyHealthCareCenterList::join('sms_properties', 'smsproperty_healthcarecenterlist.smsProperty_id', '=', 'sms_properties.id')
            ->select('smsproperty_healthcarecenterlist.*', 'sms_properties.property_name')
            ->where('smsproperty_healthcarecenterlist.id', intval($id))
            ->get();

        return view('sms.editTxt', compact('txt'));
    }

    /**
     * @param Requests\SmsPropertyHealthCareCenterRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function editTxtSend(Requests\SmsPropertyHealthCareCenterRequest $request)
    {
        $id = (new SecurityBundle\SBInput())->getNumberInt($request->get('id'));
        $sms = SmsPropertyHealthCareCenterList::findOrFail($id);
        $sms->update([
            'status' => $request->get('status'),
            'value' => $request->get('value') ? $request->get('value') : null,
            'sms_message' => $request->get('sms_message')
        ]);

        return redirect('sms/txt');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function setting()
    {
        $setting = SmsSetting::where('healthCareCenterList_id', $this->hospitalAuthUser())
            ->get();

        return view('sms.setting', compact('setting'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editSetting($id)
    {
        $setting = SmsSetting::where([
                ['healthCareCenterList_id', $this->hospitalAuthUser()],
                ['id', intval($id)]
            ])
            ->get();

        return view('sms.editSetting', compact('setting'));
    }

    /**
     * @param Requests\SmsSettingRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function editSettingSend(Requests\SmsSettingRequest $request)
    {
        $filter = new SecurityBundle\SBInput();
        $settingId = $filter->getNumberInt($request->get('id'));
        $status = $filter->getNumberInt($request->get('status'));
        $setting = SmsSetting::findOrFail($settingId);

        if ($this->smsSettingValidate() > 0 && intval($status) == 1){
            \Session::flash('error_msg', 'فقط یک اکانت می تواند فعال باشد');
            return redirect('sms/setting');
        }else{
            $setting->update([
                'name' => $filter->getString($request->get('name')),
                'username' => $filter->getString($request->get('username')),
                'password' => $filter->getString($request->get('password')),
                'line_number' => $filter->getString($request->get('line_number')),
                'status' => $status
            ]);

            return redirect('sms/setting');
        }
    }

    /**
     * @return mixed
     */
    private function smsSettingValidate()
    {
        return SmsSetting::where([
            ['healthCareCenterList_id', $this->hospitalAuthUser()],
            ['status', 1]
        ])->count();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function settingAdd()
    {
        return view('sms.settingAdd');
    }

    /**
     * @param Requests\SmsSettingRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function settingAddSend(Requests\SmsSettingRequest $request)
    {
        $filter = new SecurityBundle\SBInput();

        $setting['status']                  = $filter->getNumberInt($request->get('status'));
        $setting['healthCareCenterList_id'] = $this->hospitalAuthUser();
        $setting['name']                    = $filter->getString($request->get('name'));
        $setting['username']                = $filter->getString($request->get('username'));
        $setting['password']                = encrypt($filter->getString($request->get('password')));
        $setting['line_number']             = $filter->getString($request->get('line_number'));

        if ($this->smsSettingValidate() > 0 && intval($setting['status']) == 1){
            \Session::flash('error_msg', 'فقط یک اکانت می تواند فعال باشد');
        }else {
            SmsSetting::create($setting);
        }
        return redirect('sms/setting');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function smsList()
    {
        $list = HospitalSms::join('sms_statuses', 'hospital_sms.delivery_status', '=', 'sms_statuses.code')
            ->select('hospital_sms.*', 'sms_statuses.message')
            ->where('hospital_id', $this->hospitalAuthUser())
            ->orderby('id', 'desc')
            ->paginate(10);

        return view('sms.list', compact('list'));
    }


    /**
     * @param $hospitalId
     */
    public function cronJob($hospitalId)
    {
        $hospitalID = (new SecurityBundle\SBInput())->getNumberInt($hospitalId);

        $day = (int)SmsPropertyHealthCareCenterList::where([
            ['smsProperty_id', 2],
            ['healthcareCenterList_id', $hospitalID],
            ['status', 1]
        ])->value('value');

        $union = HospitalQueue::whereBetween('forDate', [date('Y-m-d', time()), date('Y-m-d', strtotime(" + $day days")) ])
            ->where([
                ['hospital_id', $hospitalID],
                ['sms_status', 0],
                ['flag', 1]
            ]);

//        $union->update(['sms_status' => 1]);

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

        $msg = SmsPropertyHealthCareCenterList::where([
            ['smsProperty_id', 2],
            ['status', 1],
            ['healthcareCenterList_id', $hospitalID]
        ])->value('sms_message');

        $smsResult = $this->sendSMS([$numbers], $msg ."-". $hospital  . "- دکتر " . $sms_doctorLastName ." -  تاریخ : ". $sms_date);

        $this->sms['hospital_id'] = $hospitalID;
        $this->sms['mobiles'] = $mobiles;
        $this->sms['rec_id'] = $smsResult['recId'];
        $this->sms['delivery_status'] = $smsResult['delivery'];
        $this->sms['created_at'] = Carbon::now();

        HospitalSms::create($this->sms);

        $this->updateHospitalQueue($hospitalID, $day);
//        $updateQuery->update(['sms_status' => 1]);
    }


    /**
     * @param $hospitalID
     * @param $day
     */
    private function updateHospitalQueue($hospitalID, $day)
    {
        HospitalQueue::whereBetween('forDate', [date('Y-m-d', time()), date('Y-m-d', strtotime(" + $day days")) ])
            ->where([
                ['hospital_id', intval($hospitalID)],
                ['sms_status', 0],
                ['flag', 1]
            ])
            ->update(['sms_status' => 1]);
    }


}
