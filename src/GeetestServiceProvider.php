<?php
namespace Junliuxian\Geetest;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Junliuxian\Geetest\Facades\Geetest as GeetestFacade;

class GeetestServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    protected $defer = true;

    /**
     * @inheritdoc
     */
    public function boot(Request $request)
    {
        Validator::extend('geetest', function ($attribute, $value, $parameters, $validator) use($request){

            $gt   = $request->session()->get('geetest');
            $data = ['user_id'=>$gt['user_id'], 'client_type'=>$gt['client_type'], 'ip'=>$request->ip()];

            return GeetestFacade::validate($gt['status'], $value,
                                    $request->input('geetest_validate'),
                                    $request->input('geetest_seccode'),$data);
        });
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(Geetest::class, function ($app) {
            return new Geetest(env('GEETEST_ID'), env('GEETEST_KEY'));
        });
    }

    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [Geetest::class];
    }
}