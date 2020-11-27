<div class="message-wrapper">
    <ul class="messages">
    	@foreach($message as $key=>$messages)
	        <li class="message clearfix">
	            <div class="{{$messages->from == Auth::User()->id ? 'sent' : 'received'}}">
	                <p>{{$messages->message}}</p>
	                <p class="date">{{date('d M Y, h:i a', strtotime($messages->created_at))}}</p>
	            </div>
	        </li>
        @endforeach
    </ul>
</div>

<div class="input-text">
    <input type="text" name="message" class="submit">
</div>