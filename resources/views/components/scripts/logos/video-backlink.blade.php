<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<style>
    /* Ensure proper centering of controls */
    #controls{{ $id }} {
        position: relative; /* Remove absolute positioning for better centering */
        max-width: 100%;

        display: flex;
        justify-content: space-between;
        align-items : center;
        padding-left : 15px;
        padding-right : 15px;
        gap:10px;
        background: rgba(0, 0, 0, 0.6);
        border-radius: 30px;
        z-index: 10;
    }

    .control-btn {
        background: rgba(0, 0, 0, 0.4);
        border: none;
        color: white;
        padding: 12px;
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
        gap: 5px;
    }

    #volume-slider{{ $id }} {
        width: 100px;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
    }

    #fullscreen-btn{{ $id }}, #mute-btn{{ $id }}, #play-btn{{ $id }}, #pause-btn{{ $id }} {
        font-size: 20px;
        color: white;
        cursor: pointer;
    }
</style>

<div id="controls{{ $id }}"  class="controls">
    <button id="play-btn{{ $id }}"  class="control-btn"><i class="fas fa-play"></i></button>
    <button id="pause-btn{{ $id }}" class="control-btn" style="display: none;"><i class="fas fa-pause"></i></button>
    <button id="stop-btn{{ $id }}" class="control-btn"><i class="fas fa-stop"></i></button>

    <div class="volume-control">
      <button id="mute-btn{{ $id }}" class="control-btn"><i class="fas fa-volume-up"></i></button>
      <input type="range" id="volume-slider{{ $id }}"  min="0" max="1" step="0.01" value="1" />
    </div>
    <button id="fullscreen-btn{{ $id }}" class="control-btn"><i class="fas fa-expand"></i></button>
</div>
