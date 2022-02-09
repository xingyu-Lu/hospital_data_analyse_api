<?php

namespace App\Exceptions;

use Flugg\Responder\Exceptions\Http\HttpException;

class BaseException extends HttpException
{
    protected $status = 400;

    protected $errorCode = 10000;

    protected $message = '参数错误';

    public function __construct($params=[])
    {
        if(!is_array($params)){
            return;
        }
        if(array_key_exists('status',$params)){
            $this->status = $params['status'];
        }
        if(array_key_exists('msg',$params)){
            $this->message = is_array($params['msg']) ? json_encode($params['msg']) : $params['msg'];
        }
        if(array_key_exists('code',$params)){
            $this->errorCode = $params['code'];
        }

        parent::__construct();
    }
}
