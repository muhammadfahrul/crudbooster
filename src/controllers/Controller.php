<?php

namespace muhammadfahrul\crudbooster\controllers;

// use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;

// use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    // use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    public function buildLogging($type, $message){
		$path = storage_path().'/logs/'.$type;
        if (!is_dir($path)) mkdir($path);
        if ($type == 'curl'){
            $message = json_decode($message, 1);
            $message['timestamp'] = date('Y-m-d H:i:s');
            $message = json_encode($message);
        }
        $file = $path.'/'.date('Y-m-d.').'log';
        $f = fopen($file,'a');
		fwrite($f, date('H:i:s')." --- ".$message.PHP_EOL);
        fclose($f);
        chmod($file, 0777);
    }

    public function buildResponse($code=200, $status=true, $message='Success', $start=0, $data=array(), $total=false){        
        $response = new \stdClass();
        $response->code = $code;
        $response->status = $status;
        $response->message = $message;
        $response->ip = getHostByName(getHostName());

        $end = microtime(true);
        $response->latency = $end - $start;

        if (empty($data)) {
            $response->data = new \stdClass();
        } else {
            $response->data = $data;
        }
        $response->data = $this->nullToString($response->data);

        if ($total) {
            $response->total_data = count($response->data);
        }
        
        $header = (object) request()->header();
        $body = (object) request()->all();

        $this->buildLogging('api', json_encode([
            'url' => $_SERVER['REQUEST_URI'],
            'header' => $header,
            'body' => $body,
            'response' => $response
        ], JSON_PRETTY_PRINT));

        return $response;
    }

    public function nullToString($array)
    {
        if (is_object($array) && $array instanceof \Illuminate\Support\Collection) {
            foreach ($array as $keyData => $valueData) {
                if (!is_array($valueData)) {
                    $valueData = (array) $valueData;
                }
                foreach ($valueData as $key => $value) {
                    if (is_null($value)) {
                        $valueData[$key] = "";
                    }
                }

                $listData[] = $valueData;
            }
            $array = $listData;
        } else {
            if (!is_array($array)) {
                $array = (array) $array;
            }
            foreach ($array as $key => $value) {
                if (is_null($value)) {
                    $array[$key] = "";
                }
            }
        }

        if (empty($array)) {
            $array = [];
        }

        return $array;
    }

    public function buildCurl($url='', $method='POST', $payload=[], $header=[])
    {
        $curl = curl_init();

        $header = [
            'Content-Type: application/json'
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $header
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $this->buildLogging('curl', json_encode([
            'ip' => getHostByName(getHostName()),
            'url' => $url,
            'method' => $method,
            'payload' => json_encode($payload),
            'header' => json_encode($header),
            'response' => $response
        ], JSON_PRETTY_PRINT));

        return json_decode($response);
    }
}
