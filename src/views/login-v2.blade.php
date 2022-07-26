<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{cbLang("page_title_login")}} : {{Session::get('appname')}}</title>
    <meta name='generator' content='CRUDBooster'/>
    <meta name='robots' content='noindex,nofollow'/>
    <link rel="shortcut icon"
          href="{{ CRUDBooster::getSetting('favicon')?asset(CRUDBooster::getSetting('favicon')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}">

    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="{{asset('vendor/crudbooster/assets/adminlte/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <!-- Theme style -->
    <link href="{{asset('vendor/crudbooster/assets/adminlte/dist/css/AdminLTE.min.css')}}" rel="stylesheet" type="text/css"/>
    <!-- Font Family Sora -->
    <link href='https://fonts.googleapis.com/css?family=Sora' rel='stylesheet'>

    <!-- support rtl-->
    @if (in_array(App::getLocale(), ['ar', 'fa']))
        <link rel="stylesheet" href="//cdn.rawgit.com/morteza/bootstrap-rtl/v3.3.4/dist/css/bootstrap-rtl.min.css">
        <link href="{{ asset("vendor/crudbooster/assets/rtl.css")}}" rel="stylesheet" type="text/css"/>
@endif

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <link rel='stylesheet' href='{{asset("vendor/crudbooster/assets/css/main.css")}}'/>
    {{-- <script src="{{asset('vendor/crudbooster/assets/js/google-captcha.js')}}" async defer></script> --}}
    <script src="{{asset('vendor/crudbooster/assets/js/api.js')}}" async defer></script>
    <style type="text/css">
        .login-page, .register-page {
            background: {{ CRUDBooster::getSetting("login_background_color")?:'#dddddd'}};
            color: {{ CRUDBooster::getSetting("login_font_color")?:'#ffffff' }}  !important;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        .login-box, .register-box {
            background: #FFFFFF;
            border-radius: 16px;
        }

        .login-box-body {
            box-shadow: 0px 0px 50px rgba(0, 0, 0, 0.8);
            background: rgba(255, 255, 255, 0.9);
            color: {{ CRUDBooster::getSetting("login_font_color")?:'#666666' }}  !important;
            margin-top: 40%;
            border-radius: 16px;
        }

        .login-box-msg {
            font-family: Sora;
            font-style: normal;
            font-weight: 600;
            font-size: 24px;
            line-height: 30px;
            margin-top: 5%;

            color: #000000;
        }

        html, body {
            overflow: hidden;
        }
    </style>
</head>

<body class="login-page">

<div class="login-box">
    <div class="login-box-body">

        @if ( Session::get('message') != '' )
            <div class='alert alert-warning' style="
            border: 1px solid rgba(56, 146, 205, 0.2);
            box-sizing: border-box;
            border-radius: 8px;"
            >
                {{ Session::get('message') }}
            </div>
        @endif

        @if (env('CUSTOM_LOGO', false) == true)
        <div style="text-align: center; margin-bottom: 5%;">
            <a href="{{url('/')}}">
                <img title='{!!($appname == 'CRUDBooster')?"<b>CRUD</b>Booster":$appname!!}'
                src='{{ CRUDBooster::getSetting("logo")?asset(CRUDBooster::getSetting('logo')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}'
                style='max-width: 100%;max-height:170px'/>
            </a>
            <h4>PARKOUR BACK OFFICE</h4>
        </div><!-- /.login-logo -->
        @else
        <p class='login-box-msg'>LOGIN</p>
        @endif
        <form autocomplete='off' action="{{ route('postLogin') }}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

            @if(!empty(config('services.google')))

                <div style="margin-bottom:10px" class='row'>
                    <div class='col-xs-12'>

                        <a href='{{route("redirect", 'google')}}' class="btn btn-primary btn-block btn-flat"><i class='fa fa-google'></i>
                            Google Login</a>

                        <hr>
                    </div>
                </div>
            @endif

            <div class="form-group has-feedback" style="margin-top: 3%;">
                <label for=""
                style="
                font-family: Sora;
                font-style: normal;
                font-weight: normal;
                font-size: 14px;
                line-height: 18px;
                /* identical to box height */


                color: #777777;"
                >Email</label>
                <input autocomplete='off' type="text" class="form-control" name='email' value="{{ old('email') }}" required
                style="
                background: #FFFFFF;
                border: 1px solid rgba(56, 146, 205, 0.2);
                box-sizing: border-box;
                border-radius: 8px;
                padding-top: 20px;
                padding-bottom: 20px;"
                />
                {{-- <span class="glyphicon glyphicon-user form-control-feedback"></span> --}}
            </div>
            <div class="form-group has-feedback" style="margin-top: 5%;">
                <label for=""
                style="
                font-family: Sora;
                font-style: normal;
                font-weight: normal;
                font-size: 14px;
                line-height: 18px;
                /* identical to box height */


                color: #777777;"
                >Password</label>
                <input autocomplete='off' type="password" class="form-control" name='password' required
                style="
                background: #FFFFFF;
                border: 1px solid rgba(56, 146, 205, 0.2);
                box-sizing: border-box;
                border-radius: 8px;
                padding-top: 20px;
                padding-bottom: 20px;"
                />
                {{-- <span class="glyphicon glyphicon-lock form-control-feedback"></span> --}}
            </div>
            @if (env('RECAPTCHA'))
            <div style="margin-bottom:13px" class='row'>
                <div class='col-xs-12'>
                    <center>
                        <div class="g-recaptcha" style="transform:scale(1);-webkit-transform:scale(1);transform-origin:0 0;-webkit-transform-origin:0 0;" data-sitekey="{{env('RECAPTCHA_SITE_KEY')}}">
                    </center>
                </div>
            </div>
            @endif
            <div class='row'>
                <div class='col-xs-12'>
                    <button type="submit" class="btn btn-primary btn-block btn-flat" style="
                    background: #3892CD;
                    border-radius: 8px;
                    padding-top: 10px;
                    padding-bottom: 10px;
                    font-family: Sora;
                    font-style: normal;
                    font-weight: bold;
                    font-size: 20px;
                    line-height: 25px;

                    color: #FFFFFF;"
                    >Login</button>
                </div>
            </div>

            {{-- <div class='row'>
                <div class='col-xs-12' align="center"><p style="padding:10px 0px 10px 0px">{{cbLang("text_forgot_password")}} <a
                                href='{{route("getForgot")}}'>{{cbLang("click_here")}}</a></p></div>
            </div> --}}
        </form>


        <br/>
        <!--a href="#">I forgot my password</a-->

    </div><!-- /.login-box-body -->

</div><!-- /.login-box -->


<!-- jQuery 2.2.3 -->
<script src="{{asset('vendor/crudbooster/assets/adminlte/plugins/jQuery/jquery-2.2.3.min.js')}}"></script>
<!-- Bootstrap 3.4.1 JS -->
<script src="{{asset('vendor/crudbooster/assets/adminlte/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
</body>
</html>
