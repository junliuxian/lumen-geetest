<?php
namespace Junliuxian\Geetest\Traits;

use Illuminate\Http\Request;
use Junliuxian\Geetest\Geetest as GT;

trait Geetest
{
    /**
     * 获取极验验证码
     *
     * @param GT $gt
     * @param Request $request
     * @return array
     */
    public function getGeetest(GT $gt, Request $request)
    {
        $param    = ['user_id'=> $this->getGeetestUid($request), 'client_type'=>$this->getGeetestClientType()];
        $response = $gt->preProcess( array_merge($param, ['ip_address'=>$request->ip()]));

        $request->session()->put('Geetest', array_merge($param, ['status'=>$response['success']]));
        return $response;
    }

    /**
     * 获取极验用户标识
     *
     * @param Request $request
     * @return int
     */
    protected function getGeetestUid($request)
    {
        $user = $request->user();

        if (isset($user->id)) {
            return $user->id;
        }

        return isset($user->uid) ? $user->uid : -1;
    }

    /**
     * 获取极验客户端类型
     * web(pc浏览器)、h5(手机浏览器、包括webview)、native(原生app)、unknown(未知)
     * 
     * @return string
     */
    protected function getGeetestClientType()
    {
        return 'web';
    }
}