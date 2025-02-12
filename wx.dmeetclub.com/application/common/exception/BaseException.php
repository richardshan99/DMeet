<?php

namespace app\common\exception;

use think\Exception;
use think\exception\HttpResponseException;
use think\Response;

/**
 * Class BaseException
 * 自定义异常类的基类
 */
class BaseException extends Exception
{
    public $code = -1;
    public $message = 'invalid parameters';

    /**
     * 构造函数，接收一个关联数组
     * @param array $params 关联数组只应包含code、msg，且不应该是空值
     */
    public function __construct($params = [])
    {
        if (!is_array($params)) {
            return;
        }
        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }
        if (array_key_exists('msg', $params)) {
            $this->message = $params['msg'];
        }

        $response = Response::create(['code'=>$this->code, 'msg'=>$this->message, 'time'=>time(),'data'=>[]], 'json', 200)->header([]);
        throw new HttpResponseException($response);
    }
}

