<?php

namespace Modules\User\app\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Modules\User\app\Models\User;
use Modules\User\app\Models\UserInfo;
use Modules\User\app\Models\UserToken;
use Modules\User\app\Models\UserTypeMaster;
use Modules\User\app\Models\LogLogin;
use Modules\User\app\Models\SocialAccount;
use Modules\User\app\Http\Controllers\UserCommonController;

use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;



class SocialLoginController extends Controller
{

    protected $userCommonController;

    public function __construct(UserCommonController $userCommonController)
    {
        $this->userCommonController = $userCommonController;
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


    public function _registerOrLoginUser($userId)
    {

        try {

            $current_date = getCarbonObject();
            $formated_current_date = $current_date->toDateString();

            if (!empty($userId)) {

                $user = User::with(['userinfo' => function ($query) {
                    $query->select('UI_ID', 'UI_USER_ID', 'UI_USER_NAME');
                }, 'userLocation', 'userRole'])
                    ->where('USER_ID', $userId)
                    ->select('USER_ID', 'USER_EMAIL', 'USER_UUID', 'USER_TYPE', 'USER_STATUS')
                    ->get();

                log::info('user details');
                log::info($user);
                // $user = User::with(['userinfo' => function ($query) {
                //     $query->select('UI_ID', 'UI_USER_ID', 'UI_USER_NAME');
                // }, 'userLocation', 'userRole'])->where('USER_ID', $userId)->select('USER_ID', 'USER_EMAIL', 'USER_UUID', 'USER_TYPE')->get();
                $user = sizeof($user) ? $user[0] : null; // Arunji 
                log::info('user check');
                log::info($user);
                // $user = $user !== null && $user->isNotEmpty() ? $user[0] : null;

                if (!empty($user)) {
                    $userid = $user->USER_ID;
                    log::info("Ulla");
                    log::info($user->USER_STATUS);
                    if (1 == $user->USER_STATUS) {
                        $Loginfo = LogLogin::where('LOG_USER_ID', $userid)
                            ->update(['LOG_STATUS' => 0]);

                        $user['id'] = $userid;
                        $user['apitoken'] = $this->_getUserToken($userid);
                        $user['name'] = _getFullUserName($user);
                        $user['email'] = $user->USER_EMAIL;
                        $user['type'] = _getUserRole($user->USER_TYPE);
                        $currentTime = Carbon::now();
                        $user['currentTime'] = $currentTime;

                        return $user;
                    } else {
                        return response()->json(['errmessage' => 'User account deactivated/deleted.'], 402);
                    }
                } else {
                    $ipaddress = null;
                    $CreateLog = new LogLogin();
                    $CreateLog->LOG_USER_ID = $userId;
                    $CreateLog->LOG_LOGIN_TIME = $formated_current_date;
                    $CreateLog->LOG_IPADDRESS = $ipaddress;
                    $CreateLog->LOG_STATUS = 1;
                    $CreateLog->save();

                    $Loginfo = LogLogin::where('LOG_USER_ID', '=', $userId)
                        ->where('LOG_IPADDRESS', '=', $ipaddress)
                        ->where('LOG_LOGIN_TIME', '=', $formated_current_date)
                        ->where('LOG_STATUS', '=', 1)
                        ->count();
                    if (!(empty($Loginfo))) {
                        if ($Loginfo > 5) {
                            return response()->json(['errmessage' => 'Please click Forgot password and generate'], 400);
                        }
                    }
                }
            } else {
                return response()->json(['errmessage' => 'Email is wrong.'], 400);
            }
        } catch (Exception $e) {
            Log::error('Controller - SocialLoginController, function - _registerOrLoginUser, Err:' . $e->getMessage());
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
        $tm2 = Carbon::now('UTC')->addHour(2)->format('Y-m-d H:i:s'); //date("Y-m-d H:i:s", strtotime('+3 hours'));

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

    public function redirectToProvider(String $provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function providerCallback(String $provider)

    {

        try {


            $social_user = Socialite::driver($provider)->stateless()->user();

            $account = SocialAccount::where([
                'USA_PROVIDER_NAME' => $provider,
                'USA_PROVIDER_ID' => $social_user->getId()
            ])->first();
            log::info('account');
            log::info($account);

            if ($account) {
                $userInfo =  $this->_registerorLoginUser($account->USA_ID);
                log::info("already account");
                log::info($userInfo);
                log::info("already account");
            }


            $user = User::where([
                'USER_EMAIL' => $social_user->getEmail(), 'USER_STATUS' => 1
            ])->first();

            log::info('user');
            log::info($user);
            log::info('user');

            if (!$user) {
                // dd($social_user);


                $userId = $this->userCommonController->createUser($social_user);

                log::info("user created in social controller");

                try {

                    Log::info('Inserting data into TW_USERS_SOCIAL_ACCOUNTS', [
                        'USA_USER_ID' => $social_user->getId(),
                    ]);
                    $newUserInfo = new SocialAccount();
                    $newUserInfo->USA_USER_ID = $userId;
                    $newUserInfo->USA_PROVIDER_ID = $social_user->id;
                    $newUserInfo->USA_PROVIDER_NAME = $provider;
                    $newUserInfo->USA_CREATED_BY = $social_user->id;
                    $newUserInfo->USA_CREATED_DATE = getCarbonObject();
                    $newUserInfo->save();
                    Log::info('social acnt created');

                    // SocialAccount::create([
                    //     'USA_USER_ID' => $userId,
                    //     'USA_PROVIDER_ID' => $social_user->getId(),
                    //     'USA_PROVIDER_NAME' => $provider,
                    //     'USA_CREATED_BY' => $social_user->getEmail(),
                    //     'USA_CREATED_DATE' => now(),
                    // ]);
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                }



                $userInfo = $this->_registerorLoginUser($userId);
            }
            $redirectUrl = 'https://talentflicks.com/QuizRedirect.html';
            $redirectUrl .= '?tok=' . urlencode($userInfo['apitoken']);


            // Redirect to the URL with user details
            return redirect()->to($redirectUrl);
        } catch (Exception $e) {
            Log::error('Controller - SocialLoginController, function - providerCallback, Err:' . $e->getMessage());
        }
    }
}
