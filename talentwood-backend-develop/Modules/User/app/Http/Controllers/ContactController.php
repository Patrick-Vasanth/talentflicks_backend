<?php

namespace Modules\User\app\Http\Controllers;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Modules\User\app\Emails\ContactMail;
use Illuminate\Support\Facades\Validator;
use Modules\User\app\Emails\ContactusMail;
use Modules\User\app\Emails\InterestedMail;
use Modules\User\app\Models\ContactUs as ModelsContactUs;
use Modules\User\app\Models\InterestedUser;

class ContactController extends Controller
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
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


    public function contactus(Request $request)
    {
        $alldata = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'message' => 'required',
        ]);

        if ($alldata->fails()) {


            return response()->json([
                'status' => 422,
                'message' => $alldata->messages()
            ]);
        } else {

            $message = ModelsContactUs::create([
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message
            ]);
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message
            ];
            // return response()->json([
            //     'status' => 200,
            //     'message' => $data
            // ]);



            if ($message) {
                Mail::to('talentflicksinfo@gmail.com')->send(new ContactusMail($data));

                return response()->json([
                    'status' => 200,
                    'message' => "Thanks for Contacting Us.. we will reach you soon..",
                    'data' => $message
                ]);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => "Internal error try again later"
                ]);
            }
        }
    }




    public function interested(Request $request)
    {
        $alldata = Validator::make($request->all(), [
            'user_name' => 'required',
            'email' => 'required',
            'mobile_number' => 'required'

        ]);
        if ($alldata->fails()) {

            return response()->json([
                'status' => 422,
                'message' => $alldata->messages()
            ]);
        } else {

            $interested = InterestedUser::create([
                'user_name' => $request->user_name,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number
            ]);

            $data = [
                'user_name' => $request->user_name,
                "email" => $request->email,
                'mobile_number' => $request->mobile_number
            ];

            if ($interested) {
                Mail::to('talentflicksinfo@gmail.com')->send(new InterestedMail($data));
                return response()->json([
                    'status' => 200,
                    'message' => "Thanks for Contacting Us.. we will reach you soon..",
                    'data' => $interested
                ]);
            }
        }
    }
}
