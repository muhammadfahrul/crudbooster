<?php namespace muhammadfahrul\crudbooster\controllers;

use CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminController extends CBController
{
    function getIndex()
    {
        $data = [];
        $data['page_title'] = '<strong>Dashboard</strong>';

        return view('crudbooster::home', $data);
    }

    public function getLockscreen()
    {

        if (! CRUDBooster::myId()) {
            Session::flush();

            return redirect()->route('getLogin')->with('message', cbLang('alert_session_expired'));
        }

        Session::put('admin_lock', 1);

        return view('crudbooster::lockscreen');
    }

    public function postUnlockScreen()
    {
        $id = CRUDBooster::myId();
        $password = request('password');
        $users = DB::table(config('crudbooster.USER_TABLE'))->where('id', $id)->first();

        if (\Hash::check($password, $users->password)) {
            Session::put('admin_lock', 0);

            return redirect(CRUDBooster::adminPath());
        } else {
            echo "<script>alert('".cbLang('alert_password_wrong')."');history.go(-1);</script>";
        }
    }

    public function getLogin()
    {

        if (CRUDBooster::myId()) {
            return redirect(CRUDBooster::adminPath());
        }

        return view('crudbooster::login');
    }

    public function postLogin()
    {
        request()->flash();
        $rules = [
            'email' => 'required|email|exists:'.config('crudbooster.USER_TABLE'),
            'password' => 'required',
        ];
        if (env('RECAPTCHA')) {
            $rules['g-recaptcha-response'] = 'required';
        }
        $validator = Validator::make(Request::all(), $rules);

        if ($validator->fails()) {
            $message = $validator->errors()->all();

            return redirect()->back()->with(['message' => implode(', ', $message), 'message_type' => 'danger']);
        }

        $email = Request::input("email");
        $password = Request::input("password");
        $users = DB::table(config('crudbooster.USER_TABLE'))->where("email", $email)->first();
        $tbUsers = DB::table('tb_users')->where('email', $email)->first();
        $merchantGroup = DB::table('tb_merchant_group')->where('merchant_group_id', $tbUsers->merchant_group_id)->first();

        if ($users->status == 'Active' && $tbUsers->is_active == true) {
        // if ($users->status == 'Active') {
            if ((!empty($tbUsers->role) && $tbUsers->role == 'ADMIN') || empty($tbUsers->role)) {
                if (\Hash::check($password, $users->password)) {
                    $priv = DB::table("cms_privileges")->where("id", $users->id_cms_privileges)->first();
        
                    $roles = DB::table('cms_privileges_roles')->where('id_cms_privileges', $users->id_cms_privileges)->join('cms_moduls', 'cms_moduls.id', '=', 'id_cms_moduls')->select('cms_moduls.name', 'cms_moduls.path', 'is_visible', 'is_create', 'is_read', 'is_edit', 'is_delete')->get();
        
                    $photo = ($users->photo) ? asset($users->photo) : asset('vendor/crudbooster/avatar.jpg');
                    Session::put('admin_id', $users->id);
                    Session::put('admin_is_superadmin', $priv->is_superadmin);
                    Session::put('admin_name', $users->name);
                    Session::put('admin_photo', $photo);
                    Session::put('admin_privileges_roles', $roles);
                    Session::put("admin_privileges", $users->id_cms_privileges);
                    Session::put('admin_privileges_name', $priv->name);
                    Session::put("user_id", $tbUsers->user_id);
                    Session::put("merchant_id", $tbUsers->merchant_id);
                    Session::put("merchant_group_id", $tbUsers->merchant_group_id);
                    Session::put("level", $merchantGroup->level);
                    Session::put('admin_lock', 0);
                    Session::put('theme_color', $priv->theme_color);
                    Session::put("appname", get_setting('appname'));
        
                    $updateUser = DB::table('tb_users')->where('email', $email)->update([
                        'login_attempt' => ($tbUsers->login_attempt + 1),
                        'last_login_at' => date('Y-m-d H:i:s'),
                        'last_login_ip' => $_SERVER['REMOTE_ADDR']
                    ]);
        
                    CRUDBooster::insertLog(cbLang("log_login", ['email' => $users->email, 'ip' => Request::server('REMOTE_ADDR')]));
        
                    $cb_hook_session = new \App\Http\Controllers\CBHook;
                    $cb_hook_session->afterLogin();
        
                    return redirect(CRUDBooster::adminPath());
                } else {
                    return redirect()->route('getLogin')->with('message', cbLang('alert_password_wrong'));
                }
            } else {
                return redirect()->route('getLogin')->with('message', 'Your role must be ADMIN !');
            }
        }else {
            return redirect()->route('getLogin')->with('message', cbLang('alert_email_not_activate'));
        }
    }

    public function getForgot()
    {
        if (CRUDBooster::myId()) {
            return redirect(CRUDBooster::adminPath());
        }

        return view('crudbooster::forgot');
    }

    public function postForgot()
    {
        $validator = Validator::make(Request::all(), [
            'email' => 'required|email|exists:'.config('crudbooster.USER_TABLE'),
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->all();

            return redirect()->back()->with(['message' => implode(', ', $message), 'message_type' => 'danger']);
        }

        $rand_string = str_random(5);
        $password = \Hash::make($rand_string);

        DB::table(config('crudbooster.USER_TABLE'))->where('email', Request::input('email'))->update(['password' => $password]);

        $appname = CRUDBooster::getSetting('appname');
        $user = CRUDBooster::first(config('crudbooster.USER_TABLE'), ['email' => g('email')]);
        $user->password = $rand_string;
        CRUDBooster::sendEmail(['to' => $user->email, 'data' => $user, 'template' => 'forgot_password_backend']);

        CRUDBooster::insertLog(cbLang("log_forgot", ['email' => g('email'), 'ip' => Request::server('REMOTE_ADDR')]));

        return redirect()->route('getLogin')->with('message', cbLang("message_forgot_password"));
    }

    public function getLogout()
    {

        $me = CRUDBooster::me();
        CRUDBooster::insertLog(cbLang("log_logout", ['email' => $me->email]));

        Session::flush();

        return redirect()->route('getLogin')->with('message', cbLang("message_after_logout"));
    }
}
