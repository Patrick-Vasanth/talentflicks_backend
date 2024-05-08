<?php

namespace Modules\Movies\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Modules\Movies\app\Models\Movies;
use Modules\Movies\app\Models\Banners;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Modules\Movies\app\Models\GoldenHour;
use PhpParser\Node\Stmt\TryCatch;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class MoviesController extends Controller
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
    /**
     * @OA\Get(
     *     path="/api/home/allmovielist",
     *     tags={"Movies"},
     *     summary="Get all the movie list",
     *     description="Return All the movie list",
     *
     *    @OA\Response(
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


    public function allmovielist()
    {

        try {
            $movie_list = Movies::all();

            if ($movie_list->isEmpty()) {
                return RB::asSuccess(400)
                    ->withData($movie_list)
                    ->withMessage('No movie found')
                    ->build();
            }
            return RB::asSuccess(200)
                ->withData($movie_list)
                ->withMessage('all movie lists')
                ->build();
        } catch (Exception $e) {
            return RB::asError(400)
                ->withData()
                ->withMessage('model not found.')
                ->build();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/home/movielist",
     *     tags={"Movies"},
     *     summary="Get movielist which is active for this week",
     *     description="Return All the movie list which is active",
     *
     *    @OA\Response(
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

    public function movielist(Request $request)
    {

        $userId = _getUserDetailsByToken($request);
        try {
            $movie_list = Movies::where('MOVIE_STATUS', 1)
                // ->with('goldenhour')
                ->with(['goldenhour', 'prebooktickets' => function ($query) use ($userId) {
                    $query->where('PREBOOK_USER_ID', $userId);
                }])->get();


            if ($movie_list->isEmpty()) {
                return RB::asSuccess(400)
                    ->withData($movie_list)
                    ->withMessage('No movie found')
                    ->build();
            }
            return RB::asSuccess(200)
                ->withData($movie_list)
                ->withMessage('movie lists')
                ->build();
        } catch (Exception $e) {
            return RB::asError(400)
                ->withData()
                ->withMessage('model not found.')
                ->build();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/home/banners",
     *     tags={"Movies"},
     *     summary="Get banner list",
     *     description="Return All the banners list which is active",
     *
     *    @OA\Response(
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

    public function banners()
    {

        try {
            $banners = Banners::where('BANNER_IS_ACTIVE', 1)->get();

            if ($banners->isEmpty()) {
                return RB::asSuccess(200)
                    ->withData($banners)
                    ->withMessage('No Banner found')
                    ->build();
            }
            return RB::asSuccess(200)
                ->withData($banners)
                ->withMessage('banner lists')
                ->build();
        } catch (Exception $e) {

            return RB::asError(400)
                ->withData()
                ->withMessage('model not found.')
                ->build();
        }
    }



    /**
     * @OA\Get(
     *     path="/api/home/movielist/{id}",
     *     tags={"Movies"},
     *     summary="Get movie based on Id",
     *     description="Return the movie corresponding to the Id",
     *
     * 
     *   @OA\Parameter(
     *          name="id",
     *          description="Id of the movie",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *    @OA\Response(
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

    public function moviedetails($id)
    {
        try {
            // $movie = Movies::with('goldenhour')->where('MOVIE_ID', $id)->firstOrFail();  //MOVIE WITH GOLDEN HOUR
            $movie = Movies::where('MOVIE_ID', $id)->firstOrFail(); //MOVIE WITHOUT GOLDEN HOUR




            return RB::asSuccess(200)
                ->withData($movie)
                ->withMessage('Movie found')
                ->build();
        } catch (ModelNotFoundException $e) {
            return RB::asSuccess(200)
                ->withData()
                ->withMessage('Movie not found')
                ->build();
        }
    }


    /**
     * @OA\Get(
     *     path="/api/home/search",
     *     tags={"Movies"},
     *     summary="Search movies by name",
     *     description="Search for the movie based on the movie name query string",
     *
     * 
     *   @OA\Parameter(
     *          name="name",
     *          description="search movie name query",
     *          required=true,
     *          in="query",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *    @OA\Response(
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


    public function searchMovies(Request $request)
    {

        $query = $request->input('name');

        try {
            // Perform the movie search based on the provided query
            $movies = Movies::where('MOVIE_NAME', 'like', '%' . $query . '%')->get();

            // Return a JSON response with the search results
            return response()->json([
                'status' => 200,
                'movies' => $movies
            ]);
        } catch (Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'status' => 500,
                'message' => 'An unexpected error occurred while searching for movies.'
            ], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/goldenHourMovie",
     *     tags={"Movies"},
     *     summary="Get The Current Golden Hour Movie",
     *     description="Return the Golden Hour Movie",
     *
     *    @OA\Response(
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
    public function goldenHourMovie()
    {
        try {

            $goldenhourmovie = GoldenHour::with('movie')->where("GH_STATUS", 1)->get(); // WITH MOVIE DETAILS
            // $goldehourmovie = GoldenHour::where("GH_STATUS", 1)->get(); //WITHOUT MOVIE DETAILS

            if ($goldenhourmovie->isEmpty()) {
                return RB::asSuccess(400)
                    ->withData($goldenhourmovie)
                    ->withMessage('No Golden hour movie')
                    ->build();
            }
            return RB::asSuccess(200)
                ->withData($goldenhourmovie)
                ->withMessage('')
                ->build();
        } catch (Exception $e) {
            return RB::asError(404)
                ->withData($e)
                ->withMessage('Error Occured')
                ->build();
        }
    }


    //For Admin

    //Banner Upload


    public function uploadbanner(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'BANNER_TITLE' => 'required|string|max:100',
            'BANNER_DESC' => 'required|string|max:255',
            'BANNER_IMAGE_NAME' => 'nullable',
            'BANNER_IMAGE_ALT' => 'required|string|max:100',
            'BANNER_IS_ACTIVE' => 'required|boolean'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 400,
                'error' => $validation->errors()
            ], 400);
        }


        try {
            $bannerregister = Banners::create($request->all());
            return response()->json([
                'status' => 200,
                'message' => 'Banner created successfully',
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    //Movie Upload

    public function uploadmovie(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'MOVIE_NAME' => 'required|string|max:255',
            'MOVIE_DESCRIPTION' => 'required|string',
            'MOVIE_RUNTIME' => 'required|string',
            'MOVIE_DIRECTOR_NAME' => 'required|string|max:255',
            'MOVIE_STAR_CAST' => 'required|string',
            'MOVIE_URL_LINK' => 'required|string|max:255',
            'MOVIE_GENRE' => 'required|numeric',
            'MOVIE_LANGUAGE' => 'required|numeric',
            'MOVIE_STATUS' => 'required|boolean',
            'MOVIE_BANNER' => 'required|string|max:255',
            'MOVIE_POSTER' => 'required|string|max:255',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 400,
                'movie' => $validation->errors()
            ], 400);
        }

        try {
            $movieupload = Movies::create($request->all());
            return response()->json([
                'status' => 201,
                'message' => 'Movie created successfully',
                'movie' => $movieupload,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 400,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function redirectgoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
    public function googlecallback()
    {

        try {
            //code...
            $google_user = Socialite::driver('google')->stateless()->user();


            $redirectUrl = 'http://localhost:3000/home';
            $redirectUrl .= '?name=' . urlencode($google_user->name);
            $redirectUrl .= '&email=' . urlencode($google_user->email);

            return redirect()->to($redirectUrl);
            // $homePageUrl = 'http://localhost:3000/home';

            // return response()->json([
            //     'name' => $google_user->name,
            //     'email' => $google_user->email,
            //     'home_page_url' => $homePageUrl,
            //     // Add any additional user information you want to send to the frontend
            // ]);
        } catch (Exception $e) {
            //throw $th;
            // dd($e->getMessage());

            return response()->json([
                'status' => 400,
                'message' => 'google user',
                'movie' => $e->getMessage()
            ], 200);
        }
    }
}
