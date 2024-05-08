<?php

namespace Modules\User\app\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Webpatser\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\User\app\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\User\app\Models\UserOtp;
use Illuminate\Http\RedirectResponse;
use Modules\User\app\Models\UserInfo;
use Modules\User\app\Models\UserRole;
use Modules\User\app\Models\UserToken;
use Modules\User\app\Models\UserLocation;

use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('user::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user::create');
    }


    /**
     * @OA\Post(
     *     path="/api/user/signup",
     *     tags={"User"},
     *     summary="Store the user information",
     *     description="Returns the user information",
     *
     *     @OA\Parameter(
     *          name="userid",
     *          description="User Id (give 0 when creation and give User id when update)",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * 
     *     @OA\Parameter(
     *          name="email",
     *          description="email",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *
     *     @OA\Parameter(
     *          name="username",
     *          description="username",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *     @OA\Parameter(
     *          name="uniqueusername",
     *          description="uniqueusername",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *
     *     @OA\Parameter(
     *          name="locality",
     *          description="locality",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *     @OA\Parameter(
     *          name="district",
     *          description="district",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *     @OA\Parameter(
     *          name="state",
     *          description="state",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *     @OA\Parameter(
     *          name="phone",
     *          description="primaryphone",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *     @OA\Parameter(
     *          name="dob",
     *          description="dob",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *     @OA\Parameter(
     *          name="gender",
     *          description="gender (Male/Female/Others)",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     * 
     *     @OA\Parameter(
     *          name="userroles",
     *          description="userroles ('1,2')",
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     * 

     *
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

    public function store(Request $request)
    {
        try {
            // $request->validate([
            //     'createdby' => 'required|numeric'
            // ]);
            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateTimeString();
            $createdby = $request->input('createdby');
            $userId = $request->input('userid');

            $create = false;
            if (0 == $userId) {
                $create = true;
                $result = $this->userStore($request);
                if ($result == 'parameter missing' || $result == 'errmessage' || $result == 'Email Id already exists' || $result == 'Mobile already exists') {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage($result)
                        ->build();
                } else {
                    $userId = $result['0']->USER_ID;
                }
            } else {
                $result = $this->userUpdate($request);
                if ($result == 'parameter missing' || $result == 'errmessage' || $result == 'Email Id already exists' || $result == 'Mobile already exists') {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage($result)
                        ->build();
                }
            }

            $CreateUserInfo = UserInfo::firstOrNew(array('UI_USER_ID' => $userId));
            $CreateUserInfo->UI_USER_ID = $userId;
            $CreateUserInfo->UI_USER_NAME = $request->input('username');
            $CreateUserInfo->UI_DOB = $request->input('dob');
            $CreateUserInfo->UI_GENDER = $request->input('gender');
            $CreateUserInfo->UI_ABOUT = $request->input('about');

            if ($CreateUserInfo->UI_ID && $CreateUserInfo->UI_ID != '') {
                $CreateUserInfo->UI_MODIFIED_BY = $createdby;
                $CreateUserInfo->UI_MODIFIED_DATE = $current_date;
            } else {
                $CreateUserInfo->UI_CREATED_BY = $createdby;
                $CreateUserInfo->UI_CREATED_DATE = $current_date;
            }


            $CreateUserInfo->save();



            $CreateUserLocation = UserLocation::firstOrNew(array('UL_USER_ID' => $userId));
            $CreateUserLocation->UL_USER_ID = $userId;
            $CreateUserLocation->UL_ADDRESS = $request->input('locality');
            $CreateUserLocation->UL_CITY = $request->input('district');
            $CreateUserLocation->UL_STATE = $request->input('state');
            $CreateUserLocation->UL_ZIPCODE = $request->input('zipcode');
            $CreateUserLocation->UL_COUNTRY = $request->input('country');
            $CreateUserLocation->UL_CREATED_BY = $createdby;
            $CreateUserLocation->UL_CREATED_DATE = $current_date;
            $CreateUserLocation->save();

            if ($request->input('userroles')) {
                $userrolesArr = explode(',', $request->input('userroles'));
                foreach ($userrolesArr as $userrole) {
                    $CreateUserRole = UserRole::firstOrNew(array('USR_USER_ID' => $userId, 'USR_USER_ROLE_ID' => $userrole));
                    $CreateUserRole->USR_USER_ID = $userId;
                    $CreateUserRole->USR_USER_ROLE_ID = $userrole;

                    if ($CreateUserRole->USR_ID && $CreateUserRole->USR_ID != '') {
                        $CreateUserRole->USR_MODIFIED_BY = $createdby;
                        $CreateUserRole->USR_MODIFIED_DATE = $current_date;
                    } else {
                        $CreateUserRole->USR_CREATED_BY = $createdby;
                        $CreateUserRole->USR_CREATED_DATE = $current_date;
                    }
                    $CreateUserRole->save();
                }
            }



            //$user = UserInfo::with('createdby', 'updatedby', 'user', 'userLocation', 'userRole')->where('UI_USER_ID', $userId)->get();

            $message = $create ? 'User created successfully' : 'User modified successfully';
            return RB::asSuccess(200)
                ->withData()
                ->withMessage($message)
                ->build();
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - store, Err:' . $e->getMessage());
            return RB::asError(400)
                ->withData()
                ->withMessage('User model not found')
                ->build();
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('user::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('user::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }


    public function userStore(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                //'usertype' => 'required|numeric',
                //'createdby' => 'required|numeric',
            ]);

            $userid = _getUserDetailsByToken($request);
            $origin = $request->header('Referer');
            $origin = substr($origin, 0, strlen($origin) - 1);

            $user = User::where('USER_ID', $userid)->first();


            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = array(); //remember to declare $pass as an array
            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            $password = md5(trim(implode($pass)));

            $current_date = getCarbonObject();
            $userDetails = User::where('USER_EMAIL', '=', $request->input('email'))->select('USER_EMAIL')->get();
            if (sizeof($userDetails)) {
                return 'Email Id already exists';
            }

            $userphoneDetails = User::where('USER_PHONE', '=', $request->input('phone'))->select('USER_PHONE')->get();
            if (sizeof($userphoneDetails)) {
                return 'Mobile already exists';
            }

            $CreateUser = new User();
            $CreateUser->USER_EMAIL = $request->input('email');
            $CreateUser->USER_NAME = $request->input('uniqueusername');
            $CreateUser->USER_PHONE = $request->input('phone');
            $CreateUser->USER_PASSWORD = $password;
            $CreateUser->USER_TYPE = 1;
            $CreateUser->USER_CREATED_BY = $request->input('createdby');
            $CreateUser->USER_CREATED_DATE = $current_date;
            $CreateUser->USER_UUID = Uuid::generate();
            $CreateUser->save();



            $reUser = array();
            $reUser[0] = $CreateUser;
            $token = '';
            $token = $this->_getUserToken($CreateUser->USER_ID);
            $user1 = array();
            $reUser['api_token'] = $token;


            return $reUser;
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - store, Err:' . $e->getMessage());

            return 'errmessage';
        }
    }


    public function userUpdate(Request $request)
    {
        try {

            $request->validate([
                'userid' => 'required|numeric|exists:TW_USERS,USER_ID',
                'email' => 'required|email',
                // 'usertype' => 'required|numeric',
            ]);


            $userId = $request->input('userid');
            $loginuserid = _getUserDetailsByToken($request);
            if ($loginuserid) {
                $loginUser = User::where('USER_ID', '=', $loginuserid)->select('USER_ID', 'USER_TYPE', 'USER_UUID')->get();

                $current_date = getCarbonObject();

                if (!empty($request->input('password'))) {
                    // $pass = md5($password);
                    $UpdateUser = User::where('USER_ID', '=', $userId)
                        ->update(['USER_PASSWORD' => $request->input('password')]);
                }

                $newUserType = $request->input('usertype');



                $checkEmailExists = User::where('USER_EMAIL', '=', $request->input('email'))->where('USER_ID', '!=', $userId)->where('USER_STATUS', '!=', 2)->get();
                if (!sizeof($checkEmailExists)) {


                    $UpdateUser = User::where('USER_ID', '=', $userId)->update(['USER_EMAIL' => $request->input('email'), 'USER_TYPE' => $newUserType, 'USER_MODIFIED_BY' => $request->input('modifiedby'), 'USER_MODIFIED_DATE' => $current_date]);
                    //$getuser = User::where('USER_ID', '=', $userId)->get();




                    // return $getuser;
                } else {
                    return 'Email Id already exists';
                }


                $checkPhoneExists = User::where('USER_PHONE', '=', $request->input('phone'))->where('USER_ID', '!=', $userId)->where('USER_STATUS', '!=', 2)->get();
                if (!sizeof($checkPhoneExists)) {


                    $UpdateUser = User::where('USER_ID', '=', $userId)->update(['USER_PHONE' => $request->input('phone'), 'USER_TYPE' => $newUserType, 'USER_MODIFIED_BY' => $request->input('modifiedby'), 'USER_MODIFIED_DATE' => $current_date]);
                    $getuser = User::where('USER_ID', '=', $userId)->get();




                    return $getuser;
                } else {
                    return 'Mobile already exists';
                }
            } else {
                return RB::asError(401)
                    ->withData()
                    ->withMessage('Unauthorized token.')
                    ->build();
            }
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - update, Err:' . $e->getMessage());

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


    /**
     * @OA\Post(
     *     path="/api/user/sendotp",
     *     tags={"User"},
     *     summary="send otp",
     *     description="Returns the validated user data along with the api token",
     *
     *     @OA\Parameter(
     *          name="userfield",
     *          description="User Email / mobile number",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *   
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

    public function requestOtp(Request $request)
    {

        try {

            $userField = $request->input('userfield');

            $checkedField = checkEmailorMobile($userField);

            if ($checkedField == 'email') {
                $user = User::where('USER_EMAIL', $userField)->where('USER_STATUS', '=', 1)->select('USER_ID')->first();


                if ($user) {
                    $userId = $user->USER_ID;
                    $otpData = generateOtp($userId, $userField);
                    // send otp in the email
                    $mail_details = [
                        'subject' => 'Testing Application OTP',
                        'body' => 'Your OTP is : ' . $otpData->UO_OTP
                    ];

                    // \Mail::to($userField)->send(new sendEmail($mail_details));

                    return RB::asSuccess(200)
                        ->withData()
                        ->withMessage('OTP sent successfully ' . $otpData->UO_OTP)
                        ->build();
                } else {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage('Invalid user.')
                        ->build();
                }
            } elseif ($checkedField == 'mobile') {

                $user = User::where('USER_PHONE', $userField)->where('USER_STATUS', '=', 1)->select('USER_ID')->first();


                if ($user) {
                    $userId = $user->USER_ID;
                    $otpData = generateOtp($userId, $userField);
                    // send otp in the email
                    $smsInfo = [
                        'userId' => $userId,
                        'body' => 'Your OTP is : ' . $otpData->UO_OTP,
                        'toNumber' => $userField,
                    ];

                    //_sendSMS($smsInfo);

                    return RB::asSuccess(200)
                        ->withData()
                        ->withMessage('OTP sent successfully ' . $otpData->UO_OTP)
                        ->build();
                } else {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage('Invalid user.')
                        ->build();
                }
            } else {
                return RB::asError(400)
                    ->withData()
                    ->withMessage('Invalid data.')
                    ->build();
            }
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - requestOtp, Err:' . $e->getMessage());
            return RB::asError(400)
                ->withData()
                ->withMessage('model not found.')
                ->build();
        }
    }


    /**
     * @OA\Post(
     *     path="/api/user/verifyotp",
     *     tags={"User"},
     *     summary="send otp",
     *     description="Returns the validated user data along with the api token",
     *
     *     @OA\Parameter(
     *          name="userfield",
     *          description="User Email / mobile number",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
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
    public function verifyOtp(Request $request)
    {

        try {

            $userField = $request->input('userfield');
            $userOtp = $request->input('userOtp');

            $checkedField = checkEmailorMobile($userField);

            if ($checkedField == 'email') {
                $user = User::where('USER_EMAIL', $userField)->where('USER_STATUS', '=', 1)->select('USER_ID')->first();


                if ($user) {
                    $otpData = validateOtp($user->USER_ID, $userOtp);

                    if ($otpData['verified']) {
                        return RB::asSuccess(200)
                            ->withData()
                            ->withMessage($otpData['message'])
                            ->build();
                    } else {
                        return RB::asError(400)
                            ->withData()
                            ->withMessage($otpData['message'])
                            ->build();
                    }
                } else {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage('Invalid user.')
                        ->build();
                }
            } elseif ($checkedField == 'mobile') {

                $user = User::where('USER_PHONE', $userField)->where('USER_STATUS', '=', 1)->select('USER_ID')->first();


                if ($user) {
                    $otpData = validateOtp($user->USER_ID, $userOtp);

                    if ($otpData['verified']) {
                        return RB::asSuccess(200)
                            ->withData()
                            ->withMessage($otpData['message'])
                            ->build();
                    } else {
                        return RB::asError(400)
                            ->withData()
                            ->withMessage($otpData['message'])
                            ->build();
                    }
                } else {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage('Invalid user.')
                        ->build();
                }
            } else {
                return RB::asError(400)
                    ->withData()
                    ->withMessage('Invalid data.')
                    ->build();
            }
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - requestOtp, Err:' . $e->getMessage());
            return RB::asError(400)
                ->withData()
                ->withMessage('model not found.')
                ->build();
        }
    }


    /**
     * @OA\Post(
     *     path="/api/user/forgetpassword",
     *     tags={"User"},
     *     summary="send otp",
     *     description="Returns the validated user data along with the api token",
     *
     *     @OA\Parameter(
     *          name="userfield",
     *          description="User Email / mobile number",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
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
    public function selfpasswordreset(Request $request)
    {
        try {
            // $request->validate([
            //     'password' => 'required',
            //     'createdBy' => 'required|numeric',
            // ]);
            //  $originalPassword = getOriginalString($request->input('password'));
            if (!isValidPassword($request->input('password'))) {

                return RB::asError(400)
                    ->withData()
                    ->withMessage('Invalid Passowrd.')
                    ->build();
            }


            $userField = $request->input('userfield');
            $browser = $request->input('browserName');
            $system = $request->input('operatingSystem');
            $originalPassword = $request->input('password');

            $checkedField = checkEmailorMobile($userField);
            $checkuser = array();
            if ($checkedField == 'email') {
                $checkuser = User::where('USER_EMAIL', $userField)->where('USER_STATUS', '=', 1)->select('USER_ID')->first();
            } elseif ($checkedField == 'mobile') {

                $checkuser = User::where('USER_PHONE', $userField)->where('USER_STATUS', '=', 1)->select('USER_ID')->first();
            }

            if (!empty($checkuser)) {


                $userId = $checkuser->USER_ID;
                $userOtp = UserOtp::where('UO_USER_ID', $userId)->where('UO_IS_VERIFIED', 1)->orderBy('UO_ID', 'DESC')->first();

                if ($userOtp) {
                    $current_date = getCarbonObject();
                    if (!empty($request->input('password'))) {
                        $UpdateUser = User::where('USER_ID', '=', $userId)
                            ->update(['USER_PASSWORD' => $originalPassword, 'USER_MODIFIED_BY' => $request->input('createdBy')]);
                    }

                    return RB::asSuccess(200)
                        ->withData()
                        ->withMessage('Password changed successfully.')
                        ->build();
                } else {
                    return RB::asError(400)
                        ->withData()
                        ->withMessage('Invalid.')
                        ->build();
                }
            }
        } catch (Exception $e) {
            Log::error('Controller - UserController, function - selfpasswordreset, Err:' . $e->getMessage());

            return RB::asError(400)
                ->withData()
                ->withMessage('model not found')
                ->build();
        }
    }


    public function getUserInfo($userId)
    {

        try {
            $user_info = User::where('USER_ID', $userId)->with('userPrebookTickets')->get();
            if (!$user_info) {
                return RB::asError(400)
                    ->withData()
                    ->withMessage('User not found')
                    ->build();
            }
            return RB::asSuccess(200)
                ->withData($user_info)
                ->withMessage('user details with Prebooked tickets')
                ->build();
        } catch (Exception $e) {

            return RB::asSuccess(200)
                ->withData($e)
                ->withMessage('error')
                ->build();
        }
    }
}
    

    //     if ($user_info->isEmpty()) {
    //         return RB::asSuccess(200)
    //             ->withData($user_info)
    //             ->withMessage('No Golden hour movie')
    //             ->build();
    //     }
    //     return RB::asSuccess(200)
    //         ->withData($user_info)
    //         ->withMessage('')
    //         ->build();
    // }
