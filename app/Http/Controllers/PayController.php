<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MohammadReza\SecurityBundle;
use App\Http\Requests;
use App\Doctor;
use App\HospitalQueue;
use App\PoliclinicQueue;
use Illuminate\Support\Facades\Session;

class PayController extends Controller
{
    /**
     * @var int
     */
    private $doctor_id;

    /**
     * @var int
     */
    private $hospital_price;

    /**
     * @var int
     */
    private $policlinic_price;


    /**
     * @param $id
     */
    public function payHospital($id)
    {
        $security = new SecurityBundle\SBInput();
        $this->doctor_id = $security->getNumberInt($id);
        if (\Session::has('hospitalQueueCode')) {
            $this->hospital_price = Doctor::where('id', $this->doctor_id)->value('hospitalVisitPrice');
            $sth = json_decode($this->send("test", $this->hospital_price, "f.dev:8080/pay/redirect"));
            $this->hospitalUpdateQueue($this->hospital_price, $sth->transId);
        }
        echo $this->verify("test", $sth->transId);
    }


    /**
     * @param $id
     */
    public function PayPoliclinic($id)
    {
        $security = new SecurityBundle\SBInput();

        $this->doctor_id = $security->getNumberInt($id);

        if (session()->has('policlinicQueueCode')) {
            $this->policlinic_price = Doctor::where('id', $this->doctor_id)->value('clinicVisitPrice');
            $sth = json_decode($this->send("test", $this->policlinic_price, "f.dev:8080/pay/redirect"));
            $this->policlinicUpdateQueue($this->policlinic_price, $sth->transId);
        }

        echo $this->verify("test", $sth->transId);
    }



    /**
     * @param $amount
     * @param $transId
     */
    private function hospitalUpdateQueue($amount, $transId)
    {
        $hospital = HospitalQueue::where('trackingCode', session()->get('hospitalQueueCode'))->get();
        if($hospital){
            HospitalQueue::where('trackingCode', session()->get('hospitalQueueCode'))->update(['transId' => $transId, 'amount' => $amount]);
            \Session::forget('hospitalQueueCode');
            \Session::flush();
        }
    }


    /**
     * @param $amount
     * @param $transId
     */
    private function policlinicUpdateQueue($amount, $transId)
    {
        $policlinic = PoliclinicQueue::where('trackingCode', session()->get('policlinicQueueCode'))->get();

        if ($policlinic) {

            PoliclinicQueue::where('trackingCode', session()->get('policlinicQueueCode'))->update(['transId' => $transId, 'amount' => $amount]);
            session()->forget('policlinicQueueCode');

        }
    }

}