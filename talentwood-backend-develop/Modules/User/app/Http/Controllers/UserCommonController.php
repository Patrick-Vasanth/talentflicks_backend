<?php

namespace Modules\User\app\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\User\app\Models\User;
use Modules\User\app\Models\UserInfo;
use Modules\User\app\Models\UserToken;
use Modules\User\app\Models\UserTypeMaster;
use Modules\User\app\Models\UserRoleMaster;
use Modules\User\app\Models\StateMaster;
use Modules\User\app\Models\CityMaster;
use Uuid;

use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Modules\User\app\Models\UserRole;

class UserCommonController extends Controller
{




    public function createUser($userData)
    {
        try {
            // Log::info('usercreate');
            $createUser = new User();
            $createUser->USER_EMAIL = $userData->getEmail();
            $createUser->USER_NAME = $userData->name;
            $password = generatePassword();
            $createUser->USER_PASSWORD = hashedstring($password);
            //$createUser->USER_CREATED_BY = $userData->createdBy;
            $createUser->USER_CREATED_DATE = getCarbonObject();
            $createUser->USER_TYPE = 1;
            $createUser->USER_UUID = generateUUID();
            $createUser->save();
            Log::info('stored');
            $userId = $createUser->USER_ID;
            $this->updateUserInfo($userData, $userId);

            Log::info('update');
            return $userId;
        } catch (Exception $e) {
            Log::error('Controller - UserCommonController, function - createUser, Err:' . $e->getMessage());

            return response(['error' => 'Cannot create new user'], 400);
        }
    }

    public function updateUserInfo($userInfoObj, $userId)
    {
        try {

            $newUserInfo = new UserInfo();
            $newUserInfo->UI_USER_ID = $userId;
            $newUserInfo->UI_USER_NAME = $userInfoObj->getName();
            $newUserInfo->UI_CREATED_DATE = getCarbonObject();
            $newUserInfo->save();
        } catch (Exception $e) {
            Log::error('Controller - UserCommonController, function - updateUserInfo, Err:' . $e->getMessage());

            return response(['error' => 'Cannot update user info'], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user/attributes",
     *     tags={"userAttributes"},
     *     summary="Get all the user attributes details",
     *     description="Returns tall the user attributes details",
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

    public function getUserAttributes(Request $request)
    {
        try {
            $return = [];
            $userType = UserTypeMaster::where('UT_STATUS', '=', 1)->select('UT_ID', 'UT_NAME')->get();
            $return['userType'] = $userType;
            $userRole = UserRoleMaster::where('UR_STATUS', '=', 1)->select('UR_ID', 'UR_NAME')->get();
            $return['userRole'] = $userRole;
            $stateCity = StateMaster::with('citymaster')->select('STATE_ID', 'STATE_NAME')->get();
            $return['stateCity'] = $stateCity;


            return RB::asSuccess(200)
                ->withData($return)
                ->withMessage('')
                ->build();
        } catch (Exception $e) {
            Log::error('Controller - UserCommonController, function - getUserAttributes, Err:' . $e->getMessage());
            return RB::asError(400)
                ->withData()
                ->withMessage('user attributes model not found')
                ->build();
        }
    }
}
