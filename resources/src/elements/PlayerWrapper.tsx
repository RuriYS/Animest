import React, { useEffect, useRef } from 'react';
import {
    isHLSProvider,
    MediaPlayer,
    MediaPlayerInstance,
    MediaProvider,
    MediaProviderAdapter,
    MediaProviderChangeEvent,
} from '@vidstack/react';
import Hls, { HlsConfig } from 'hls.js';
import {
    defaultLayoutIcons,
    DefaultVideoLayout,
} from '@vidstack/react/player/layouts/default';

import '@vidstack/react/player/styles/default/theme.css';
import '@vidstack/react/player/styles/default/layouts/video.css';

interface MediaPlayerWrapperProps {
    src: string;
    title: string;
    onEnd: () => void;
}

const config: Partial<HlsConfig> = {
    autoStartLoad: true,
    debug: false,
    maxBufferLength: Infinity,
    maxBufferSize: 200 * 1000 * 1000,
    maxBufferHole: 0.5,
    lowLatencyMode: true,
    highBufferWatchdogPeriod: 1,
    nudgeOffset: 0.1,
    nudgeMaxRetry: 5,
    maxFragLookUpTolerance: 0.1,
    liveSyncDurationCount: 3,
    liveMaxLatencyDurationCount: 4,
    fragLoadingTimeOut: 20000,
    fragLoadingMaxRetry: 2,
    fragLoadingRetryDelay: 1000,
    fragLoadingMaxRetryTimeout: 5000,
    startFragPrefetch: true,
    progressive: true,
    abrEwmaDefaultEstimate: 500000,
    abrEwmaFastLive: 3,
    abrEwmaSlowLive: 9,
};

function onProviderChange(
    provider: MediaProviderAdapter | null,
    _nativeEvent: MediaProviderChangeEvent,
) {
    if (isHLSProvider(provider)) {
        console.info('Player', 'Changed HLS Provider');
        provider.library = Hls;
        provider.config = config;
    }
}

const PlayerWrapper: React.FC<MediaPlayerWrapperProps> = ({
    src,
    title,
    onEnd,
}) => {
    const playerRef = useRef<MediaPlayerInstance>(null);

    useEffect(() => {
        if (playerRef.current) {
            const player = playerRef.current;
            player.addEventListener('ended', onEnd);
            return () => {
                player.removeEventListener('ended', onEnd);
            };
        }
    }, [onEnd]);

    const _URL = new URL(src);

    return (
        <MediaPlayer
            className='max-h-screen'
            viewType='video'
            streamType='on-demand'
            ref={playerRef}
            onEnd={onEnd}
            title={title}
            src={`/proxy/${_URL}`}
            playsInline
            onProviderChange={onProviderChange}
        >
            <MediaProvider />
            <DefaultVideoLayout icons={defaultLayoutIcons} />
        </MediaPlayer>
    );
};

export default PlayerWrapper;
