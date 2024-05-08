<?php

namespace Modules\Movies\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Movies\app\Models\QuizCompetitionModel;
use Modules\Movies\app\Models\QuizModel;
use Modules\Movies\app\Models\QuizQuestionModel;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Modules\Movies\app\Models\QuizTest;
use Modules\Movies\app\Models\QuizTestAnswer;

class QuizController extends Controller
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

    public function getQuiz()
    {
    }

    public function testingquizz(Request $request)
    {
        try {

            // All data with competition and also question and anss
            // $questionsWithComp = QuizCompetitionModel::with(['questions.options'])
            //     ->where('comp_status', 1)
            //     ->get();

            // $questionsWithOptions = QuizQuestionModel::with('options')
            //     ->whereHas('competition', function ($query) {
            //         $query->where('comp_status', 1);
            //     })->first();


            // $competition = QuizCompetitionModel::with('questions.options')
            //     ->where('comp_status', 1)->first();

            $competition = QuizCompetitionModel::with(['questions' => function ($query) {
                $query->with(['options' => function ($query) {
                    $query->select('qo_id', 'qo_qt_id', 'qo_option_value'); // Exclude qo_correct column
                }]);
            }])->where('comp_status', 1)->first();

            if ($competition) {
                $questionsWithOptions = $competition->questions;
                // return response()->json([
                //     'status' => 200,
                //     'CompQuestion' => $questionsWithOptions
                // ], 200);
                return RB::asSuccess(200)
                    ->withData($questionsWithOptions)
                    ->withMessage('Questions')
                    ->build();
            }

            return response()->json([
                'status' => 404,
                'message' => "No Quiz Available"
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e,
            ], 500);
        }
    }


    //User Test Creation 

    public function usertest(Request $request)
    {


        $validation = Validator::make($request->all(), [
            'competition_id' => 'required|numeric',
            'average_score' => 'nullable|numeric',
            'test_score' => 'nullable|numeric',
            'result' => 'nullable|numeric',
            'quiz_start_by' => 'nullable|integer',
            'quiz_approved_by' => 'nullable|integer',
        ]);

        if ($validation->fails()) {

            return RB::asError(400)
                ->withMessage($validation->errors())
                ->build();
        }
        try {
            $userId = _getUserDetailsByToken($request);

            // Check if a test already exists for the given competition and user
            $existingTest = QuizTest::where('user_id', $userId)
                ->where('competition_id', $request->input('competition_id'))
                ->exists();

            if ($existingTest) {

                return RB::asError(400)
                    ->withMessage('A test for this competition already exists for the user.')
                    ->build();
            }

            $requestData = $request->all();
            $requestData['user_id'] = $userId;
            $user_test = QuizTest::create($requestData);


            return RB::asSuccess(201)
                ->withData($user_test)
                ->withMessage("User test created")
                ->build();
        } catch (Exception $e) {

            return RB::asError(400)
                ->withMessage($e->getMessage())
                ->build();
        }
    }
    public function compdetails()
    {

        try {
            $comp_details = QuizCompetitionModel::where('comp_status', 1)->get();

            return RB::asSuccess(200)
                ->withData($comp_details)
                ->withMessage('comp_details')
                ->build();
        } catch (Exception $e) {
            return RB::asError(400)
                ->withData()
                ->withMessage($e->getMessage())
                ->build();
        }
    }

    public function quizAnswer(Request $request)
    {

        try {
            $validatedData = $request->validate([
                'test_id' => 'required|integer',
                'answers.*.question_id' => 'required|integer',
                'answers.*.option_id' => 'required|integer',
            ]);
            $userId = _getUserDetailsByToken($request);
            $existingTest = QuizTestAnswer::where('user_id', $userId)
                ->where('test_id', $validatedData['test_id'])
                ->exists();

            if ($existingTest) {
                return RB::asError(400)
                    ->withMessage('User has already attended a test.')
                    ->build();
            }

            $questionIds = collect($validatedData['answers'])->pluck('question_id');
            $questionsWithOptions = QuizQuestionModel::whereIn('qt_id', $questionIds)
                ->with('options')
                ->get()
                ->keyBy('qt_id');

            Log::info($questionsWithOptions);

            $correctOptions = QuizQuestionModel::whereIn('qt_id', $questionIds)
                ->with('options')
                ->get()
                ->flatMap(function ($question) {
                    return [$question->qt_id => $question->options->where('qo_correct', 1)->pluck('qo_id')->toArray()];
                });

            // Process and store the answers
            $answers = [];  //working one
            foreach ($validatedData['answers'] as $answer) {
                // $isCorrect = in_array($answer['option_id'], $correctOptions[$answer['question_id']]);
                // Log::info($isCorrect);

                $question = $questionsWithOptions[$answer['question_id']];
                $correctOptionId = $question->options->where('qo_correct', 1)->pluck('qo_id')->first();
                $isCorrect = $answer['option_id'] == $correctOptionId;

                $answers[] = [
                    'user_id' => $userId,
                    'test_id' => $validatedData['test_id'],
                    'question_id' => $answer['question_id'],
                    'correct' => $isCorrect ? 1 : 0,
                    'option_id' => $answer['option_id'],
                    'created_at' => now(),
                ];
            }


            QuizTestAnswer::insert($answers);

            // Calculate the total number of correct answers
            $totalCorrect = collect($answers)->where('correct', 1)->count();

            QuizTest::where('id', $validatedData['test_id'])
                ->where('user_id', $userId)
                ->update(['result' => $totalCorrect]);

            // $totalCorrect = $answers->where('correct', 1)->count();
            return RB::asSuccess(200)
                ->withMessage('User Test answers stored successfully')
                ->build();


            // Check if the user has already submitted answers for the given test_id
            // $existingAnswers = QuizTestAnswer::where('user_id', $validatedData['user_id'])
            //     ->where('test_id', $validatedData['test_id'])
            //     ->exists();

            // if ($existingAnswers) {
            //     return RB::asError(400)
            //         ->withData()
            //         ->withMessage('You have already taken this quiz.')
            //         ->build();
            // }


            // If the user hasn't already taken the quiz, store their answers
            // foreach ($validatedData['answers'] as $answer) {
            //     QuizTestAnswer::create([
            //         'user_id' => $validatedData['user_id'],
            //         'test_id' => $validatedData['test_id'],
            //         'question_id' => $answer['question_id'],
            //         'option_id' => $answer['option_id'],
            //         'correct' => $answer['is_correct'],
            //     ]);
            // }
            // return RB::asSuccess(200)
            //     ->withData()
            //     ->withMessage('Answer Submitted successfully')
            //     ->build();
        } catch (Exception $e) {
            return RB::asError(400)
                ->withData()
                ->withMessage($e->getMessage())
                ->build();
        }
    }
}
