# lumen-geetest
极验验证码，Lumen 框架简洁实现

# 安装
- 使用 `composer` 快速安装

    `composer require junliuxian/geetest`

-  在 `bootstrap/app.php` 文件中添加这一行。注意，你还需要开启 `Session`

    `$app->register(Junliuxian\Geetest\GeetestServiceProvider::class);`

- 启用 `session` 服务，在 `bootstrap/app.php` 中添加以下代码

    ```
       $app->configure('session');
       $app->alias('session', Illuminate\Session\SessionManager::class); 
       
       $app->middleware([
           Illuminate\Session\Middleware\StartSession::class,
       ]);
       
       $app->register(Illuminate\Session\SessionServiceProvider::class);
    ```

# 使用

- 在 `.env` 文件中添加配置

    ```
    GEETEST_ID=
    GEETEST_KEY=
    ```

- 在控制器中引用 `Geetest`

    ```
    namespace App\Http\Controllers;
    
    use Laravel\Lumen\Routing\Controller;
    use Junliuxian\Geetest\Traits\Geetest;
    
    class LoginController extends Controller
    {
        use Geetest;
    }
    ```

- 在 `routes\web.php` 文件中添加路由

    ```
    $router->post('captcha', 'LoginController@getGeetest');
    ```  

- 客户端部署，参考 [geetest](https://docs.geetest.com/install/deploy/client/web)

- 二次验证
  
      ```
      namespace App\Http\Controllers;
      
      use Laravel\Lumen\Routing\Controller;
      use Junliuxian\Geetest\Traits\Geetest;
      
      class LoginController extends Controller
      {
          use Geetest;
          
          public function login(Request $request)
          {
              $this->validate($request, [
                   // 注意，验证的字段必须是 challenge
                  'geetest_challenge' => 'geetest'
              ]);
          }
      }
      ```  