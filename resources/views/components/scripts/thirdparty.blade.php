{{--@formatter:off--}}
{{--<script>--}}
;(function () {
var w=window,d=document,url='{{ $trigger_url }}&t='+Date.now();
var impr=function(){new Image().src=url};
var click=function(){new Image().src=url+'&c=1'};
var iv=function(t,width,height){
var e=window,i=document,n=!1,r=getComputedStyle(t);
if((t.offsetWidth<width)||(t.offsetHeight<height))return n;
if("none"===r.display||"visible"!==r.visibility||r.opacity<.1)return n;
var o=t.getBoundingClientRect();if(t.offsetWidth+t.offsetHeight+o.height+o.width===0)return n;
var f={x:o.left+t.offsetWidth/2,y:o.top+t.offsetHeight/2};if(f.x<0)return n;
var d=i.documentElement;if(f.x>(d.clientWidth||e.innerWidth)||f.y<0||f.y>(d.clientHeight||e.innerHeight))return n;
var h=i.elementFromPoint(f.x,f.y);do if(h===t)return!0;while((h=h.parentNode));
return n};
var div=d.createElement('div');
div.id="d_{{ $rand }}";
div.style.cssText='margin:0;padding:0;border:none';
div.innerHTML=`{!! $code !!}`;
div.onclick=function(){if(iv(div,{{e($ad->adType->width)}},{{e($ad->adType->height)}})){click()}};
d.getElementById('{{$dom_id}}').insertAdjacentElement('afterend',div);
var ss=div.getElementsByTagName('script');
for(var i=0;i<ss.length;i+=1){
    var ss1=ss[i];
    if(ss1.src){
        var ss2=d.createElement('script');
        for(var j=0;j<ss1.attributes.length;j+=1){ss2[ss1.attributes.item(j).name]=ss1.attributes.item(j).value;}
        div.insertBefore(ss2,ss1);
        ss1.remove();
    }
    eval(ss1.innerHTML)
};
@if($ad->adType->isBanner())
var i=w.setInterval(function(){if(iv(div,{{e($ad->adType->width)}},{{e($ad->adType->height)}})){w.clearInterval(i);impr()}},50);
@else
var i=w.setInterval(function(){
    if(iv(div,{{e($ad->adType->width)}},{{e($ad->adType->height)}})){
        w.clearInterval(i);
        var vs=div.getElementsByTagName('video');
        if(vs.length>0){
            var v=vs[0],s=null,tp=0,dd=Date;
            i=setInterval(function(){
                if(!v.paused&&!v.ended&&v.readyState>2&&v.currentTime>0){if(s===null){s=dd.now()}else{tp+=dd.now()-s;s=dd.now()}}
                else if(s!==null){tp+=dd.now()-s;s=null}
                if(tp>={{ config('ads.ads.videos.player_impression_delay') }}||tp==v.duration*1000){w.clearInterval(i);impr()}
            },10);
        }
    }
},50);
@endif
})();
{{--@formatter:on--}}
