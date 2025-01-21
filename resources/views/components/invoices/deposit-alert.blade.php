<div role="alert" class="m-2 alert alert-secondary alert-dismissible">
    <h4 class="alert-heading">{{ $title ?? 'Invoice Created' }}</h4>
    <hr>
    <p class="mb-0">
        You have just created an invoice <b style="color:#80cf21">{{$base_amount}}</b><br>
        Please make the required transaction in less than <b id="expiry_{{$id}}"
                                                             style="color:yellow">{{(int)($expiry_secs/60)}}</b> minutes<br>
        Please send <b style="color:#80cf21">{{$crypto_amount}}</b> to address below.<br>
        Address: <b style="color:#80cf21">{{$wallet}}</b>
    </p>
    <button type="button" class="close m-2" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
    @isset($countdown)
        <script>Ads.Utils.countdown($('#expiry_{{$id}}'), {{$expiry_secs * 1000}}, '00:00 <i>Expired</i>');</script>@endisset
</div>