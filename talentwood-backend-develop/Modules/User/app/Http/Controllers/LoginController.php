<?php

namespace Modules\User\app\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Modules\User\app\Models\User;
use Modules\User\app\Models\UserInfo;
use Modules\User\app\Models\UserToken;
use Modules\User\app\Models\UserTypeMaster;
use Modules\User\app\Models\LogLogin;
use Modules\User\app\Http\Controllers\UserController;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

/**
 * @OA\Info(
 *    title="Swagger with Laravel",
 *    version="1.0.0",
 * )
 */
class LoginController extends Controller
{

    protected $userController;

    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
        $this->current_date = getCarbonObject();
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('crm::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('crm::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('crm::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('crm::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }


    /**
     * @OA\Post(
     *     path="/api/user/login",
     *     tags={"User"},
     *     summary="Allow the user to login",
     *     description="Returns the validated user data along with the api token",
     *
     *     @OA\Parameter(
     *          name="email",
     *          description="User Email Id",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *     @OA\Parameter(
     *          name="password",
     *          description="Encoded User Password",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function login(Request $request)
    {

        try {
            // $request->validate([
            //     'email' => 'required|email',
            //     'password' => 'required',
            // ]);

            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateString();
            $ipaddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $request->ip();

            $email = $request->input('email');
            //  $originalPassword = getOriginalString($request->input('password'));
            $pass = $request->input('password');
            $rememberMe = ($request->input('rememberMe')) ? ($request->input('rememberMe')) : 0;
            $emaildata = User::where('USER_EMAIL', '=', $email)->first();


            if (!empty($emaildata)) {
                if (1 == $emaildata->USER_STATUS) {
                    $userid = $emaildata->USER_ID;
                    $usertype = $emaildata->USER_TYPE;
                    $user = User::with(['userinfo' => function ($query) {
                        $query->select('UI_ID', 'UI_USER_ID', 'UI_USER_NAME');
                    }, 'userLocation', 'userRole', 'userRole.userrolemaster'])->where('USER_PASSWORD', '=', $pass)->where('USER_ID', '=', $userid)->select('USER_ID', 'USER_EMAIL', 'USER_UUID', 'USER_TYPE')->get();
                    $user = sizeof($user) ? $user[0] : null;


                    if (!empty($user)) {
                        $Loginfo = LogLogin::where('LOG_USER_ID', '=', $userid)
                            ->update(['LOG_STATUS' => 0]);

                        $user['id'] = $user->USER_ID;
                        $user['apitoken'] = $this->_getUserToken($userid, $rememberMe);
                        $user['name'] = _getFullUserName($user);
                        $user['email'] = $user->USER_EMAIL;
                        $user['type'] = _getUserRole($user->USER_TYPE);
                        $currentTime = Carbon::now();
                        $user['currentTime'] = $currentTime;
                        //  $user['usertypes'] = $this->_getUserType();

                        return RB::asSuccess(200)
                            ->withData($user)
                            ->withMessage('')
                            ->build();
                    } else {
                        $CreateLog = new LogLogin();
                        $CreateLog->LOG_USER_ID = $userid;
                        $CreateLog->LOG_LOGIN_TIME = $formated_current_date;
                        $CreateLog->LOG_IPADDRESS = $ipaddress;
                        $CreateLog->LOG_STATUS = 1;
                        $CreateLog->save();

                        $Loginfo = LogLogin::where('LOG_USER_ID', '=', $userid)
                            ->where('LOG_IPADDRESS', '=', $ipaddress)
                            ->where('LOG_LOGIN_TIME', '=', $formated_current_date)
                            ->where('LOG_STATUS', '=', 1)
                            ->count();
                        if (!(empty($Loginfo))) {
                            if ($Loginfo > 5) {
                                return RB::asError(400)
                                    ->withData()
                                    ->withMessage('Please click Forgot password and generate')
                                    ->build();
                            }
                        }
                        return RB::asError(400)
                            ->withData()
                            ->withMessage('Password is wrong')
                            ->build();
                    }
                } else {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage('User account deactivated/deleted.')
                        ->build();
                }
            } else {
                return RB::asError(400)
                    ->withData()
                    ->withMessage('Email is wrong.')
                    ->build();
            }
        } catch (Exception $e) {
            Log::error('Controller - LoginController, function - login, Err:' . $e->getMessage());
            return RB::asError(400)
                ->withData()
                ->withMessage('model not found')
                ->build();
        }
    }


    /**
     * To generate dynamic token while logining in
     * @param $uid
     * @return generated token
     */
    private function _getUserToken($uid, $isRemember = 0)
    {
        $tok = 0;
        $rnd = rand(1000, 5000);
        // $tok = md5(trim(substr(base64_encode($rnd), 0, 8)));
        $tm1 = Carbon::now('UTC')->format('Y-m-d H:i:s'); //date("Y-m-d H:i:s");
        $randomBytes = random_bytes(16); // Generate 16 random bytes using a cryptographically secure random number generator
        $randomString = bin2hex($randomBytes); // Convert the random bytes to a hexadecimal representation
        $tok = md5(trim(base64_encode($uid . $tm1 . $randomString)));
        $tm2 = Carbon::now('UTC')->addHour(3)->format('Y-m-d H:i:s'); //date("Y-m-d H:i:s", strtotime('+3 hours'));

        $tokObj = new UserToken();
        $tokObj->UT_TOKEN = $tok;
        $tokObj->UT_USER_ID = $uid;
        $tokObj->UT_CREATED_DATE = $tm1;
        $tokObj->UT_DELETED_DATE = null;
        $tokObj->UT_EXPIRE_DATE = $tm2;
        $tokObj->UT_IS_REMEMBER = $isRemember;
        $tokObj->save();

        return $tok;
    }

    public function _getUserType()
    {
        $usertypes = UserTypeMaster::select('UT_ID', 'UT_NAME', 'UT_STATUS')->get();
        return $usertypes;
    }

    /**
     * This REST resource To logout user
     * @param  \Illuminate\Http\Request  $request
     * @return Response as JSON Object
     */
    public function logout(Request $request)
    {
        try {
            $tokid = $request->header('apitoken');
            $tm1 = date("Y-m-d H:i:s");
            $tokObj = UserToken::where('UT_TOKEN', '=', $tokid)->update(['UT_DELETED_DATE' => $tm1]);
            return RB::asSuccess(401)
                ->withData()
                ->withMessage('User Logged out successfully.')
                ->build();
        } catch (Exception $e) {
            Log::error('Controller - LoginController, function - logout, Err:' . $e->getMessage());
            return RB::asSuccess(400)
                ->withData()
                ->withMessage('Cannot able to log out')
                ->build();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/user/loginwithotp",
     *     tags={"User"},
     *     summary="Allow the user to login with otp",
     *     description="Returns the validated user data along with the api token",
     *
     *     @OA\Parameter(
     *          name="userphone",
     *          description="User mobile number",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *     @OA\Parameter(
     *          name="userOtp",
     *          description="OTP",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *     @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function loginWithOtp(Request $request)
    {

        try {


            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateString();
            $ipaddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $request->ip();

            $userphone = $request->input('userphone');
            $userOtp = $request->input('userOtp');

            $user = User::where('USER_PHONE', $userphone)->where('USER_STATUS', '=', 1)->select('USER_ID')->first();


            if ($user) {
                $otpData = validateOtp($user->USER_ID, $userOtp);


                if ($otpData['verified']) {
                    $userid = $user->USER_ID;
                    $user = User::with(['userinfo' => function ($query) {
                        $query->select('UI_ID', 'UI_USER_ID', 'UI_USER_NAME');
                    }, 'userLocation', 'userRole', 'userRole.userrolemaster'])->where('USER_ID', '=', $userid)->select('USER_ID', 'USER_EMAIL', 'USER_UUID', 'USER_TYPE')->get();
                    $user = sizeof($user) ? $user[0] : null;


                    if (!empty($user)) {
                        $Loginfo = LogLogin::where('LOG_USER_ID', '=', $userid)
                            ->update(['LOG_STATUS' => 0]);

                        $user['id'] = $user->USER_ID;
                        $user['apitoken'] = $this->_getUserToken($userid);
                        $user['name'] = _getFullUserName($user);
                        $user['email'] = $user->USER_EMAIL;
                        $user['type'] = _getUserRole($user->USER_TYPE);
                        $currentTime = Carbon::now();
                        $user['currentTime'] = $currentTime;
                        //  $user['usertypes'] = $this->_getUserType();

                        return RB::asSuccess(200)
                            ->withData($user)
                            ->withMessage('')
                            ->build();
                    }
                } else {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage($otpData['message'])
                        ->build();
                }
            } else {
                return RB::asError(400)
                    ->withData()
                    ->withMessage('Mobile number is wrong.')
                    ->build();
            }
        } catch (Exception $e) {
            Log::error('Controller - LoginController, function - loginWithOtp, Err:' . $e->getMessage());
            return RB::asError(400)
                ->withData()
                ->withMessage('model not found.')
                ->build();
        }
    }
}
