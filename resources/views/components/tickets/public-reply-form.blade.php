<x-page-layout>
    <x-slot name="page_title">Reply</x-slot>

    <div class="row justify-content-center pb-4">
        <div class="card card-small col-md-6">
            <div class="card-header border-bottom row">
                <h5 class="col mb-0">Reply</h5>
            </div>
            <ul class="list-group list-group-flush mb-1">
                <li class="list-group-item">
                    @if(Session::has('success'))
                        <div class="alert alert-success">
                            {{ Session::get('success') }}
                        </div>
                    @endif
                    <form action="{{ route('contact.reply', ['thread'=>$thread->id,'hash'=>$hash]) }}" method="POST">
                        @csrf
                        <div class="form-group row">
                            <div class="col blockquote">{{ $thread->subject }}</div>
                        </div>
                        @foreach($thread->messages as $message)
                            <div class="form-group row">
                                <div class="col blockquote">
                                    <small><i>{{ isset($message->guest) ? 'You' : $message->user->name }}
                                            at {{ $message->created_at->format('Y F d H:i') }}</i></small>:<br/> {!! nl2br($message->message) !!}
                                </div>
                            </div>
                        @endforeach
                        <x-input.textarea name="message" label="Reply" value="{{ old('message') }}" required rows="10"/>
                        <div class="form-group row">
                            <div onclick="Ads.Modules.Captcha.reload(this)" class="captcha col-md-4 text-md-right"
                                 title="Reload">{!! captcha_img() !!}</div>
                            <div class="col-md-6 input-group">
                                <input class="form-control {{ $errors->has('captcha') ? 'is-invalid' : '' }}"
                                       type="text" name="captcha" placeholder="Enter Captcha" autocomplete="off"/>
                                @error('captcha')
                                <span class="invalid-feedback" role="alert">Invalid Captcha</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                        </div>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <div class="pt-4"></div>
</x-page-layout>
