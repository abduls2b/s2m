<!DOCTYPE html>
<html>
<head>
    <title>Live Tv</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: system-ui, sans-serif;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
        }
        .player-wrapper {
            width: 100%;
            max-width: 100vw;
            max-height: 100vh;
            background: #000;
            position: relative;
            overflow: hidden;
        }
        .video-container {
            position: relative;
            width: 100%;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        video {
            width: 100%;
            height: 100%;
            display: block;
            background: #000;
            object-fit: contain;
            outline: none;
        }
        /* No custom controls – only native video controls */
    </style>
</head>
<body>
<div class="player-wrapper" id="playerWrapper">
    <div class="video-container">
        <video id="video" playsinline preload="auto" controls controlslist="nodownload noremoteplayback"></video>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
(function() {
    const video = document.getElementById('video');
    const streamUrl = '<?php echo isset($_GET['stream']) ? htmlspecialchars($_GET['stream']) : 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8'; ?>';

    let hls = null;

    function initHLS() {
        if (Hls.isSupported()) {
            hls = new Hls({
                // INCREASED BUFFER SETTINGS
                maxBufferLength: 120,           // Increased from 30 to 120 seconds
                maxMaxBufferLength: 240,         // Increased from 60 to 240 seconds
                maxBufferSize: 60 * 1000 * 1000, // 60MB buffer size (was default 30MB)
                maxBufferHole: 2,                // Increased from 0.5 to 2 seconds
                backbufferLength: 60,            // Keep 60 seconds in backbuffer
                
                // Additional buffer-related settings
                liveDurationInfinity: true,       // Enable for live streams
                liveSyncDurationCount: 5,         // Keep 5 segments in buffer for live
                liveMaxLatencyDurationCount: 10,  // Max latency of 10 segments
                
                autoStartLoad: true,
                startLevel: -1,
                lowLatencyMode: true,
                enableWorker: true,
                
                // ABR settings for better buffering
                abrEwmaDefaultEstimate: 5000000,  // 5 Mbps default estimate
                abrEwmaFastLive: 3.0,             // Fast EWMA for live
                abrEwmaSlowLive: 9.0,              // Slow EWMA for live
                abrBandWidthFactor: 0.95,          // Use 95% of bandwidth
                abrBandWidthUpFactor: 1.2,         // Increase bandwidth estimate faster
            });

            hls.loadSource(streamUrl);
            hls.attachMedia(video);

            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                video.play().catch(() => {});
            });

            // Monitor buffer state
            hls.on(Hls.Events.BUFFER_APPENDED, function(event, data) {
                const buffered = video.buffered;
                if (buffered.length > 0) {
                    const bufferedEnd = buffered.end(buffered.length - 1);
                    const duration = video.duration;
                    const bufferPercentage = (bufferedEnd / duration) * 100;
                    console.log(`Buffer: ${bufferPercentage.toFixed(1)}% (${bufferedEnd.toFixed(1)}s / ${duration.toFixed(1)}s)`);
                }
            });

            hls.on(Hls.Events.ERROR, function(event, data) {
                if (data.fatal) {
                    if (data.type === Hls.ErrorTypes.NETWORK_ERROR) {
                        console.warn('Network error, retrying...');
                        hls.startLoad();
                    } else if (data.type === Hls.ErrorTypes.MEDIA_ERROR) {
                        console.warn('Media error, recovering...');
                        hls.recoverMediaError();
                    } else {
                        console.error('Fatal error, reloading stream');
                        hls.loadSource(streamUrl);
                    }
                }
            });

            window.hls = hls;

        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Native HLS (Safari)
            video.src = streamUrl;
        } else {
            video.innerHTML = '<p style="color:#fff;text-align:center;">HLS not supported</p>';
        }
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (hls) {
            hls.destroy();
        }
    });

    initHLS();
})();
</script>
</body>
</html>