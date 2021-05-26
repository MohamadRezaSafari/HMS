<?php

namespace App\Http\Controllers;

use App\SmsSetting;
use App\SmsStatus;
use App\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use GuzzleHttp\Client;
use SoapClient;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * @return mixed
     */
    protected function hospitalAuthUser()
    {
        return User::join('hcl_user', 'users.id', '=', 'hcl_user.user_id')
            ->join('healthcare_center_lists', 'hcl_user.hcl_id', '=', 'healthcare_center_lists.id')
            ->where('users.id', \Auth::id())
            ->select('healthcare_center_lists.id')
            ->value('id');
    }

    /**
     * @param $mobiles
     * @param $message
     * @return string
     */
    protected function sendSMS($mobiles, $message)
    {
        $client = new \SoapClient("http://87.107.121.52/post/send.asmx?wsdl");
        $recId = uniqid() . mt_rand();
        $setting = SmsSetting::where([
                ['healthCareCenterList_id', $this->hospitalAuthUser()],
                ['status', 1]
            ])
            ->limit(1)
            ->get();

        foreach ($setting as $item){
            $info['username']   = $item['username'];
            $info['password']   = decrypt($item['password']);
            $info['lineNumber'] = $item['line_number'];
        }
        try{
            $parameters = [
                'username' => $info['username'],
                'password' => $info['password'],
                'from' => $info['lineNumber'],
                'to' => $mobiles,
                'text' => $message,
                'isflash' => false,
                'udh' => "",
                'recId' => $recId,
                'status' => 0
            ];
            $status = $client->SendSms($parameters)->SendSmsResult;
            $smsResult = ['recId' => $recId, 'delivery' => $status];
            if ($status == 1)
                return $smsResult;
            else
                $this->smsStatus($status);
        }catch (\Exception $exception){
            echo $exception->getMessage();
            exit();
        }
    }


    /**
     * @param $number
     * @return mixed
     */
    private function smsStatus($number)
    {
        return SmsStatus::where('code', intval($number))->value('message');
    }


    /**
     * @param $api
     * @param $amount
     * @param $redirect
     * @return mixed
     */
    protected function send($api, $amount, $redirect)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pay.ir/payment/test/send');
        curl_setopt($ch, CURLOPT_POSTFIELDS,"api=$api&amount=$amount&redirect=$redirect");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }


    /**
     * @param $api
     * @param $transId
     * @return mixed
     */
    protected function verify($api, $transId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pay.ir/payment/test/verify');
        curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$api&transId=$transId");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }



}