{{-- @formatter:off --}}
{{--<script>--}}
;(function() {
    var w = window, d = document;
    var Timer = function(callback, delay) {
        var timerId, startTime, remaining = delay;
        this.paused = false;
        this.pause = function() {
            clearTimeout(timerId);
            remaining -= Date.now() - startTime;
            this.paused = true;
        };

        this.resume = function() {
            if (remaining > 0) {
                startTime = Date.now();
                timerId = setTimeout(callback, remaining);
                this.paused = false;
            } else {
                this.stop();
            }
        };

        this.stop = function() {
            clearTimeout(timerId);
            this.paused = false;
        };

        this.resume();
    };

    var isPlaying = function(videoElement) {
        return videoElement && !videoElement.paused && !videoElement.ended && videoElement.readyState > 2 && videoElement.currentTime > 0;
    };

    var setupControls = function(video) {
        const videoContainer = document.getElementById('v_{{ $rand }}');
        const controls = document.querySelector('#controls_bar{{ $rand }}');
        const playBtn = document.getElementById('play-btn{{ $rand }}');
        const pauseBtn = document.getElementById('pause-btn{{ $rand }}');
        const stopBtn = document.getElementById('stop-btn{{ $rand }}');
        const muteBtn = document.getElementById('mute-btn{{ $rand }}');
        const volumeSlider = document.getElementById('volume-slider{{ $rand }}');
        const fullscreenBtn = document.getElementById('fullscreen-btn{{ $rand }}');
        const timeline = document.getElementById('timeline{{ $rand }}');

        playBtn.addEventListener('click', () => {
            video.play();
            playBtn.style.display = 'none';
            pauseBtn.style.display = 'inline-block';
        });

        pauseBtn.addEventListener('click', () => {
            video.pause();
            playBtn.style.display = 'inline-block';
            pauseBtn.style.display = 'none';
        });

        stopBtn.addEventListener('click', () => {
            video.pause();
            video.currentTime = 0;
            playBtn.style.display = 'inline-block';
            pauseBtn.style.display = 'none';
        });

        muteBtn.addEventListener('click', () => {
            video.muted = !video.muted;
            muteBtn.innerHTML = video.muted
                ? '<i class="fas fa-volume-mute"></i>'
                : '<i class="fas fa-volume-up"></i>';
        });

        volumeSlider.addEventListener('input', (e) => {
            video.volume = e.target.value;
        });

        video.addEventListener('timeupdate', () => {
            if (!video.paused && !video.ended) {
                const progress = (video.currentTime / video.duration) * 100;
                timeline.value = progress;
            }
        });

        timeline.addEventListener('input', (e) => {
            const seekTime = (e.target.value / 100) * video.duration;
            video.currentTime = seekTime;
        });

        fullscreenBtn.addEventListener('click', () => {
            if (video.requestFullscreen) {
                video.requestFullscreen();
            } else if (video.webkitRequestFullscreen) {
                video.webkitRequestFullscreen();
            } else if (video.msRequestFullscreen) {
                video.msRequestFullscreen();
            }
        });

        // Show controls on hover
        videoContainer.addEventListener('mouseenter', () => {
            controls.style.display = 'flex'; // Show controls
        });

        // Hide controls when mouse leaves
        videoContainer.addEventListener('mouseleave', () => {
            controls.style.display = 'none'; // Hide controls
        });
    };

    var loadAd = function(vastUrl, enableTracking) {
        var timer = null,   progressTracker = null;
        var videoContainer = d.getElementById('v_{{ $rand }}');
        var vastPlayer = new VASTPlayer(videoContainer);

        vastPlayer.once('AdStopped', function() {
            vastPlayer = null;
            if (progressTracker) {
                clearInterval(progressTracker);
            }
            {{ config('ads.ads.videos.repeat') ? 'loadAd(vastUrl, false);' : '' }}
        });

        if (enableTracking) {
            vastPlayer.once('AdSkipped', function() {
                console.log('Ad skipped');
            });

            vastPlayer.once('AdUserClose', function() {
                console.log('Ad closed by user');
            });

            vastPlayer.on('AdPaused', function() {
                if (timer) timer.pause();
            });

            vastPlayer.on('AdPlaying', function() {
                if (timer && timer.paused) {
                    timer.resume();
                }
            });

            vastPlayer.once('AdVideoStart', function() {
                var trackingEvents = [];
                var videoElement = videoContainer.querySelector('video');
                const timeline = document.getElementById('timeline{{ $rand }}');

                try {
                    trackingEvents = vastPlayer.vast.ads[0].creatives[0].trackingEvents || [];
                } catch (error) {
                    console.error('VAST XML error:', error);
                }

                var progressTrackingUrls = trackingEvents
                    .filter(event => event.event === 'progress')
                    .map(event => event.uri.trim());

                if (progressTrackingUrls.length > 0) {
                    progressTracker = setInterval(function() {
                        var videoPlaying = isPlaying(videoContainer.querySelector('video'));
                        if (videoPlaying && !timer) {
                            timer = new Timer(function() {
                                progressTrackingUrls.forEach(function(url) {
                                    new Image().src = url;
                                    console.log(url);
                                });
                                 clickEnable = true;
                                console.log('click_enabled!!!');
                                console.log('{{($ad->adType->width)}} * {{($ad->adType->height)}}');
                            }, {{ config('ads.ads.videos.video_ad_event_delay') }});
                        } else if (timer) {
                            if (videoPlaying && timer.paused) {
                                timer.resume();
                            } else if (!videoPlaying && !timer.paused) {
                                timer.pause();
                            }
                        }
                    }, 50);
                }
            });
        }

        vastPlayer.load(vastUrl)
            .then(function() {
                const videoElement = videoContainer.querySelector('video');
                if (videoElement) {
                    // Resize video dynamically to 500x500 container with proper scaling
                    videoElement.style.width = "100%";
                    videoElement.style.height = "100%";
                    videoElement.style.objectFit = "cover"; // Ensures it covers the area fully
                    videoElement.muted = true;

                    setupControls(videoElement);

                    // Attempt to autoplay the video
                    videoElement.play()
                        .then(() => {
                            console.log('Video is autoplaying');
                        })
                        .catch(error => {
                            console.warn('Autoplay failed:', error);
                            videoElement.muted = false;
                            videoElement.pause();
                        });
                }

                return vastPlayer.startAd();
            })
            .catch(function(error) {
                console.error('Error loading VAST ad:', error);
            });
    };

    var initializeAd = function () {
        var adContainer = document.createElement('div');
        adContainer.style = 'position:relative;  width:{{ e($ad->adType->width) }}px;  height:{{ e($ad->adType->height) }}px;  visibility:visible; display:block;    border : 0px; background-color: #000000;' ;
        adContainer.innerHTML =`
            <div style="position:absolute; border:0px solid; width:100%; top:0px; left:0px; z-index: 1; display:flex; align-items:center; justify-content:center; background-color: rgba(0, 0, 0, 0.5);">
                {!! preg_replace('/\s+/', ' ', $logo) !!}
            </div>

            <div id="controls_bar{{ $rand }}"
                 style="position:absolute; width:100%; bottom:0px; z-index: 1; display:none; align-items:center; justify-content:center; background-color: rgba(0, 0, 0, 0.5);">
                {!! preg_replace('/\s+/', ' ', $v_controls) !!}
            </div>
        `;

        adContainer.id = 'v_{{ $rand }}';
        document.getElementById('{{ $dom_id }}').insertAdjacentElement('afterend', adContainer);
        var vastUrl = '{{ $vast_url }}';
        vastUrl += (vastUrl.includes('?') ? '&' : '?') + 't=' + Date.now() + '&h={{ $rand }}';


        adContainer.addEventListener('click', function (e) {
            if (!clickEnable && !e.target.closest('#controls{{ $rand }}')) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Ad click event prevented');
            }
        }, true);

        loadAd(vastUrl, true);
    };

    if (!!w.VASTPlayer) {
       initializeAd();
    } else {
        var script = d.createElement('script');
        var clickEnable=false;
        script.src = '{{ url('js/vast-player.js') }}';
        script.onload = initializeAd;
        d.head.appendChild(script);
    }
})();

{{-- @formatter:on --}}
{{--</script>--}}
