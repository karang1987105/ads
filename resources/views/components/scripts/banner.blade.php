{{--@formatter:off--}}
{{--<script>--}}
;(function () {
var w=window,d=document;
var url='{{ $trigger_url }}&t='+Date.now();
var impr=function(){new Image().src=url};
var click=function(){new Image().src=url+'&c=1'};
var div=d.createElement('div');
div.setAttribute("style", "width:{{e($ad->adType->width)}}px;height:{{e($ad->adType->height)}}px;visibility:visible;display:block;position:relative");
div.innerHTML='<a id="a_{{ $rand }}" href="{{ $campaign->ad->getUrl(true) }}" target="_blank" style="width:100%;height:100%;visibility:visible;display:block">\
    <img id="b_{{ $rand }}" src="{{ url('storage/' . $ad->banner->file) }}" alt="banner" width="{{ e($ad->adType->width) }}" height="{{ e($ad->adType->height) }}" \
    style="width:{{ e($ad->adType->width) }}px;height:{{ e($ad->adType->height) }}px;cursor:pointer;visibility:visible;display:block"/></a>\
    {!! preg_replace('/\s+/', ' ', $logo) !!}';
div.firstChild.onclick=click;
d.getElementById('{{$dom_id}}').insertAdjacentElement('afterend',div);
var iv=function(t){ {{--checkVisibilty--}}
    var e=window,i=document,n=!1,r=getComputedStyle(t);if("none"===r.display||"visible"!==r.visibility||r.opacity<.1)return n;
    var o=t.getBoundingClientRect();if(t.offsetWidth+t.offsetHeight+o.height+o.width===0)return n;
    var f={x:o.left+t.offsetWidth/2,y:o.top+t.offsetHeight/2};if(f.x<0)return n;
    var d=i.documentElement;if(f.x>(d.clientWidth||e.innerWidth)||f.y<0||f.y>(d.clientHeight||e.innerHeight))return n;
    var h=i.elementFromPoint(f.x,f.y);do if(h===t)return!0;while((h=h.parentNode));return n
};
var el=d.getElementById("b_{{ $rand }}");
el.onload=function(){
    if(iv(el)){impr()}else{
        var sc=function(){if(iv(el)){impr();w.removeEventListener('scroll',sc)}}
        w.addEventListener('scroll',sc);
        var rs=function(){if(iv(el)){impr();w.removeEventListener('resize',rs)}}
        w.addEventListener('resize',rs);
    }
};
})();
{{--@formatter:on--}}
