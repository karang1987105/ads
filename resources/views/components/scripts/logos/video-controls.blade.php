<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<style>
    /* Ensure proper centering of controls */
    #controls{{ $id }} {
        position: relative; /* Remove absolute positioning for better centering */
        max-width: 100%;

        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px;
        background: rgba(0, 0, 0, 0.6);
        border-radius: 30px;
        z-index: 10;
        gap: 10px;
    }

    .control-btn {
        margin : 0px;
        background: rgba(0, 0, 0, 0.4);
        border: none;
        color: white;
        padding: 10px;
        cursor: pointer;
        border-radius: 50%;
        font-size: 18px;
        transition: all 0.3s ease;

    }
    .control-btn:hover {
        background: rgba(0, 0, 0, 0.7);

        transform: scale(1.1);
    }

    .volume-control {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #volume-slider{{ $id }} {
        width: 80px;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
    }

    #fullscreen-btn{{ $id }}, #mute-btn{{ $id }}, #play-btn{{ $id }}, #pause-btn{{ $id }}, #stop-btn{{ $id }} {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.9);
        cursor: pointer;
    }

    #timeline-container{{ $id }} {
        flex-grow: 1;
        margin: 0 10px;
        display: flex;
        align-items: center;
    }

    #timeline{{ $id }} {
        width: 100%;
        appearance: none;
        background: rgba(255, 255, 255, 0.3);
        height: 5px;
        border-radius: 3px;
        cursor: pointer;
        outline: none;
        transition: all 0.3s ease;
    }

   #timeline{{ $id }}::-webkit-slider-thumb {
        appearance: none;
        width: 12px;
        height: 12px;
        background: #fff;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    #timeline{{ $id }}::-moz-range-thumb {
        width: 12px;
        height: 12px;
        background: #fff;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

</style>

<div id="controls{{ $id }}"  class="controls">
    <div id="timeline-container{{ $id }}" >
        <input
            type="range"
            id="timeline{{ $id }}"
            min="0"
            max="100"
            step="0.1"
            value="0"
        />
    </div>
    <button id="pause-btn{{ $id }}" class="control-btn" ><i class="fas fa-pause"></i></button>
    <button id="play-btn{{ $id }}"  class="control-btn" style="display: none;"><i class="fas fa-play"></i></button>
    <button id="stop-btn{{ $id }}" class="control-btn"><i class="fas fa-stop"></i></button>

    <div class="volume-control">
      <button id="mute-btn{{ $id }}" class="control-btn"><i class="fas fa-volume-mute"></i></button>
      <input type="range" id="volume-slider{{ $id }}"  min="0" max="1" step="0.01" value="1" />
    </div>
    <button id="fullscreen-btn{{ $id }}" class="control-btn"><i class="fas fa-expand"></i></button>
</div>
