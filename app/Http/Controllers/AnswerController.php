<?php
namespace App\Http\Controllers;
use App\Notifications\DeletedAnswerToQuestion;
use Illuminate\Http\Request;
use App\Answer;
use App\Question;
use Illuminate\Support\Facades\Auth;
use App\Notifications\RepliedToQuestion;
use App\Notifications\UpdatedReplyToQuestion;
use App\User;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($question)
    {
        //
        $answer = new Answer;
        $edit = FALSE;
        return view('answerForm', ['answer' => $answer,'edit' => $edit, 'question' =>$question  ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $question)
    {
        //
        $input = $request->validate([
            'body' => 'required|min:5',
        ], [
            'body.required' => 'Body is required',
            'body.min' => 'Body must be at least 5 characters',
        ]);
        $input = request()->all();
        $question = Question::find($question);
        $Answer = new Answer($input);
        $Answer->user()->associate(Auth::user());
        $Answer->question()->associate($question);
        $Answer->save();
        $trackQuestion= User::find($question->id);
        $trackQuestion->notify(new RepliedToQuestion());
        return redirect()->route('question.show',['question_id' => $question->id])->with('message', 'Saved');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($question,  $answer)
    {
        $answer = Answer::find($answer);
        return view('answer')->with(['answer' => $answer, 'question' => $question]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($question,  $answer)
    {
        //

        $answer = Answer::find($answer);
        $edit = TRUE;
        return view('answerForm', ['answer' => $answer, 'edit' => $edit, 'question'=>$question ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $question, $answer)
    {
        //
        $input = $request->validate([
            'body' => 'required|min:5',
        ], [
            'body.required' => 'Body is required',
            'body.min' => 'Body must be at least 5 characters',
        ]);
        $answer = Answer::find($answer);
        $answer->body = $request->body;
        $answer->save();
        $trackQuestion = User ::find($question);
        $trackQuestion->notify(new UpdatedReplyToQuestion());
        return redirect()->route('answer.show',['question_id' => $question, 'answer_id' => $answer])->with('message', 'Updated');

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($question, $answer)
    {
        //

        $answer = Answer::find($answer);
        $answer->delete();
        $trackQuestion=User::find($question);
        $trackQuestion->notify(new DeletedAnswerToQuestion());
        return redirect()->route('question.show',['question_id' => $question])->with('message', 'Delete');
    }
}