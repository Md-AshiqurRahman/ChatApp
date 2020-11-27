<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Auth;
use DB;
use Pusher\Pusher;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // $user = User::where('id' , '!=', Auth::User()->id)->get();
         $user = DB::select("select users.id, users.name, users.avatar, users.email, count(is_read) as unread 
        from users LEFT  JOIN  messages ON users.id = messages.from and is_read = 0 and messages.to = " . Auth::User()->id . "
        where users.id != " . Auth::id() . " 
        group by users.id, users.name, users.avatar, users.email");
        $message = Message::all();
        return view('home')
                ->with('user', $user)
                ->with('message', $message);
    }

    public function get_message($user_id){
        $my_id = Auth::User()->id;

        Message::where(['from'=> $user_id, 'to'=> $my_id])->update(['is_read' => 1]);
        $message = Message::where(function($query) use ($user_id,$my_id){
            $query->where('from',$my_id)->where('to',$user_id);
        })->orWhere(function($query) use ($user_id,$my_id){
            $query->where('from',$user_id)->where('to',$my_id);
        })->get();
        return view('message.index',['message' => $message]);
    }


    public function sendMessage(Request $request){
        $from = Auth::User()->id;
        $to = $request->receiver_id;
        $message = $request->message;
        $data = new Message();
        $data->from = $from;
        $data->to = $to;
        $data->message = $message;
        $data->is_read = 0;
        $status = $data->save();

        $options = array('cluster' =>'ap2', 'forceTLS' => true );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $data = ['from'=>$from, 'to'=>$to];
        $pusher->trigger('my-channel', 'my-event', $data);
    }
}
