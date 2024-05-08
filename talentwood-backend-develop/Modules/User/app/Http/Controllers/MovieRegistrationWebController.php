<?php

namespace Modules\User\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Modules\User\app\Models\MovieRegistrationWeb;

class MovieRegistrationWebController extends Controller
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

    public function movieregistration(Request $request)
    {
        $validation = Validator::make(($request->all()), [
            'user_name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'movie_title' => 'required|string',
            'movie_description' => 'required|string',
            'movie_link_url' => 'required|url',
            'is_paid' => 'nullable|boolean'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors()
            ], 400);
        }

        // Check if a record with the provided email exists
        $check_register_email = MovieRegistrationWeb::where('email', $request->email)->first();

        // Check if a record with the provided mobile number exists
        $check_register_mobile = MovieRegistrationWeb::where('phone_number', $request->phone_number)->first();

        if ($check_register_email && $check_register_mobile) {
            // If both email and mobile number exist
            if ($check_register_email->is_paid == 1 || $check_register_mobile->is_paid == 1) {
                // If either registration is paid, return error response
                return response()->json([
                    'message' => 'Already registered with this email and mobile number',
                    'success' => 0,
                    'data' => [
                        'email_registration' => $check_register_email,
                        'mobile_registration' => $check_register_mobile
                    ]
                ], 400);
            } else {
                // Otherwise, update the details of the existing registration based on email
                $check_register_email->update($request->all());
                return response()->json([
                    'message' => 'Details updated successfully',
                    'success' => 1,
                    'data' => $check_register_email
                ], 200);
            }
        } elseif ($check_register_email) {
            // If only email exists
            if ($check_register_email->is_paid == 1) {
                // If email registration is paid, return error response
                return response()->json([
                    'message' => 'Email already registered',
                    'success' => 0,
                    'data' => $check_register_email
                ], 400);
            } else {
                // Otherwise, update the details of the existing registration based on email
                $check_register_email->update($request->all());
                return response()->json([
                    'message' => 'Details updated successfully',
                    'success' => 1,
                    'data' => $check_register_email
                ], 200);
            }
        } elseif ($check_register_mobile) {
            // If only mobile number exists
            if ($check_register_mobile->is_paid == 1) {
                // If mobile registration is paid, return error response
                return response()->json([
                    'message' => 'Mobile number already registered',
                    'success' => 0,
                    'data' => $check_register_mobile
                ], 400);
            } else {
                // Otherwise, update the details of the existing registration based on mobile number
                $check_register_mobile->update($request->all());
                return response()->json([
                    'message' => 'Details updated successfully',
                    'success' => 1,
                    'data' => $check_register_mobile
                ], 200);
            }
        }

        // Create new registration if both email and mobile number are unique
        $registration = MovieRegistrationWeb::create($request->all());
        return response()->json([
            'message' => 'Registration successful',
            'success' => 1,
            'data' => $registration
        ], 201);



        // $check_register = MovieRegistrationWeb::where('email', $request->email)->first();

        // if ($check_register) {
        //     $check_register->update($request->all());
        //     return response()->json([
        //         'message' => 'Details Updated successfully',
        //         'data' => $check_register
        //     ], 200);
        // } else {
        //     $registration = MovieRegistrationWeb::create($request->all());
        //     return response()->json([
        //         'message' => 'Registration successful',
        //         'data' => $registration
        //     ], 201);
        // }
    }
}
