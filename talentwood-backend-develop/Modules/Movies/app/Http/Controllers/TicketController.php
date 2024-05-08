<?php

namespace Modules\Movies\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Modules\Movies\app\Models\GoldenHour;
use Modules\Movies\app\Models\PreBookTicketModel;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('movies::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('movies::create');
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
        return view('movies::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('movies::edit');
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

    public function prebook(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'PREBOOK_USER_ID' => 'required|integer',
            'PREBOOK_MOVIE_ID' => 'required|integer',
            'PREBOOK_IS_GOLDEN' => 'integer',
            'PREBOOK_STATUS' => 'integer'
        ]);

        if ($validation->fails()) {
            $err = $validation->errors()->all();
            return RB::asSuccess(400)
                ->withMessage('')
                ->withData($err)
                ->build();
        } else {
            try {
                // $prebook = new PreBookTicketModel();
                // $prebook->PREBOOK_USER_ID = $request->input('user_id');
                // $prebook->PREBOOK_MOVIE_ID = $request->input('movie_id');
                // $prebook->save();


                $is_moviegolden = GoldenHour::where('GH_MOVIE_ID', $request->input('PREBOOK_MOVIE_ID'))
                    ->where('GH_STATUS', 1)->first();
                if (!$is_moviegolden) {
                    // Movie is not available for prebooking
                    return RB::asSuccess(400)
                        ->withMessage('Movie is not available for prebooking')
                        ->build();
                } else {

                    $exist_prebook = PreBookTicketModel::where('PREBOOK_USER_ID', $request->input('PREBOOK_USER_ID'))->where('PREBOOK_MOVIE_ID', $request->input('PREBOOK_MOVIE_ID'));

                    if ($exist_prebook->exists()) {
                        // User has already prebooked the same movie
                        return RB::asError(400)
                            ->withMessage('You can only book this movie once')
                            ->build();
                    }
                    // else can create a prebook ticket
                    $prebook  = PreBookTicketModel::create($request->all());

                    return RB::asSuccess(200)
                        ->withMessage('Pre-booking successfull')
                        ->withData($prebook)
                        ->build();
                }
            } catch (Exception $e) {
                return RB::asError(404)
                    ->withMessage($e->getMessage())
                    ->build();
            }
        }
    }
}
