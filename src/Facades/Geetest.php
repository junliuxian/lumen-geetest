<?php
namespace Junliuxian\Geetest\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array preProcess($param, $newCaptcha = 1)
 * @method static bool validate($status, $challenge, $validate, $seccode, $param, $jsonFormat = 1)
 * @method static bool successValidate($challenge, $validate, $seccode, $param, $jsonFormat = 1)
 * @method static bool failValidate($challenge, $validate, $seccode)
 *
 * @see \Junliuxian\Geetest\Geetest
 */
class Geetest extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getFacadeAccessor()
    {
        return 'geetest';
    }
}