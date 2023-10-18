<?php


namespace muhammadfahrul\crudbooster\controllers;


use muhammadfahrul\crudbooster\helpers\CB;
use Illuminate\Support\Facades\Cache;
use muhammadfahrul\crudbooster\helpers\CRUDBooster;

class ApiAuthorizationController extends Controller
{
    private $ttl = 2880;
    private $token_length = 16;

    public function postGetToken() {
        $start = microtime(true);

        CB::valid(['secret'=>'required'],'json');

        $exists = db("cms_apikey")
            ->where("screetkey", g("secret"))
            ->where("status","active")
            ->count();
        if($exists) {
            $accessToken = str_random($this->token_length);;
            Cache::put("api_token_".$accessToken,[
                "ip"=> $_SERVER['REMOTE_ADDR'],
                "user_agent"=> $_SERVER['HTTP_USER_AGENT']
            ], $this->ttl);

            $code = 200;
            $status = true;
            $message = 'Success';
            $data = [
                'access_token'=>$accessToken,
                'expiry'=> strtotime("+".$this->ttl." minutes")
            ];
            $response = CRUDBooster::buildResponse($code, $status, $message, $start, $data);
            return response()->json($response);
        } else {
            $code = 400;
            $status = false;
            $message = 'Credential invalid!';
            $response = CRUDBooster::buildResponse($code, $status, $message, $start);
            return response()->json($response);
        }
    }
}