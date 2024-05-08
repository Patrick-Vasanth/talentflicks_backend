<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\RegisterPayment;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Log;
use Modules\User\app\Emails\MovieRegisterMail;
use Modules\User\app\Emails\NotifyMail;
use Modules\User\app\Models\MovieRegistrationWeb;

class PhonePecontroller extends Controller
{

  //Your Controller Method
  public function phonePePayment(Request $request)
  {

    try {
      $order_id = uniqid();
      $amount = $request->input('amount');
      $mob_no = $request->input('mob_no');
      $data = array(
        'merchantId' => config('constants.phonepe_merchant_id'),
        'merchantTransactionId' => $order_id,
        'merchantUserId' => $mob_no,
        'amount' => $amount * 100,
        'redirectUrl' => config('constants.site_url') . '/Redirect.html?id=' . $order_id,
        'redirectMode' => 'REDIRECT',
        'callbackUrl' => config('constants.site_url') . '/api/phonepe-response',
        'mobileNumber' => $mob_no,
        'paymentInstrument' =>
        array(
          'type' => 'PAY_PAGE',
        ),
      );
      $baseUrl = config('constants.phonepe_env') == 'production' ? 'https://api.phonepe.com/apis/hermes' : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
      $encode = base64_encode(json_encode($data));

      $saltKey = config('constants.phonepe_salt_key');
      $saltIndex = config('constants.phonepe_salt_index');

      $string = $encode . '/pg/v1/pay' . $saltKey;
      $sha256 = hash('sha256', $string);

      $final_x_header = $sha256 . '###' . $saltIndex;
      $request = json_encode(array('request' => $encode));
      $curl = curl_init();
      curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrl . '/pg/v1/pay',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $request,
        CURLOPT_HTTPHEADER => [
          "Content-Type: application/json",
          "X-VERIFY: " . $final_x_header,
          "accept: application/json"
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
        echo "cURL Error #:" . $err;
      } else {
        $res = json_decode($response);
        // Store information into database

        $data = [
          'RP_AMOUNT' => 10000,
          'RP_TRANSACTION_ID' => $order_id,
          'RP_MECHANT_ORDER_ID' => $mob_no,
          'RP_PAYMENT_STATUS' => 'PAYMENT_PENDING',
          'RP_RESPONSE_MSG' => $response,
          'RP_PROVIDE_REF_ID' => '',
          'RP_CHECKSUM' => ''
        ];

        RegisterPayment::create($data);

        // end database insert

        if (isset($res->code) && ($res->code == 'PAYMENT_INITIATED')) {

          $payUrl = $res->data->instrumentResponse->redirectInfo->url;
          return response()->json([
            'status' => 200,
            'url' => $payUrl
          ]);
        }
      }
    } catch (Exception $e) {
      Log::error('Controller - PhonePecontroller, function - phonePePayment, Err:' . $e->getMessage());
    }
  }


  public function callBackAction(Request $request)
  {
    try {
      Log::info('Controller - PhonePecontroller, function - callBackAction, response :' . json_encode($request->all()));

      $decodedResponse = json_decode(base64_decode($request->input('response')));
      Log::info('Controller - PhonePecontroller, function - callBackAction, response decoded :' . json_encode($decodedResponse));
      if ($decodedResponse->success) {
        $transactionId = $decodedResponse->data->merchantTransactionId;
        $merchantOrderId = ''; //$decodedResponse->merchantOrderId;
        $checksum = ''; //$decodedResponse->checksum;



        $data = [
          'RP_PROVIDE_REF_ID' => $decodedResponse->data->transactionId,
          'RP_CHECKSUM' => $checksum,
          'RP_PAYMENT_STATUS' => $decodedResponse->code,
          'RP_PAYMENT_FLAG' => 1,
          'RP_RESPONSE_MSG' => json_encode($decodedResponse)

        ];
        if ($merchantOrderId != '') {
          $data['RP_MECHANT_ORDER_ID'] = $merchantOrderId;
        }
        RegisterPayment::where('RP_TRANSACTION_ID', $transactionId)->update($data);
      }
    } catch (Exception $e) {
      Log::error('Controller - PhonePecontroller, function - callBackAction, Err:' . $e->getMessage());
    }
  }


  public function checkstatus(Request $request)
  {

    $validation = Validator::make(($request->all()), [
      'mob_no' => 'required',
      'trans_id' => 'required'
    ]);



    if ($validation->fails()) {
      return response()->json([
        'error' => $validation->errors()
      ], 400);
    } else {

      $transId = $request->trans_id;


      $payment = RegisterPayment::where('RP_TRANSACTION_ID', $transId)->first();
      if (!$payment) {
        return response()->json([
          'error' => 'Transaction ID not found'
        ], 404);
      }

      $paymentStatus = RegisterPayment::where('RP_TRANSACTION_ID', $request->trans_id)->pluck('RP_PAYMENT_STATUS')->first();




      if ($paymentStatus == "PAYMENT_SUCCESS") {

        $updatepay = MovieRegistrationWeb::where('phone_number', $request->mob_no)->first();

        if ($updatepay) {




          $updatepay->update(['is_paid' => 1]);
          $email = $updatepay->email;
          $user_name = $updatepay->user_name;


          Mail::to($email)->send(new MovieRegisterMail($user_name));
          Mail::to("talentflicksinfo@gmail.com")->send(new NotifyMail($email));


          return response()->json([
            'data' => $payment->RP_PAYMENT_STATUS,
            'message' => "Registration Successful",
            'email' => $email,
          ], 200);
        }
      }
      return response()->json([
        'data' => $payment->RP_PAYMENT_STATUS,
        'message' => "Registration not Successful"
      ], 200);
    }
  }
}
