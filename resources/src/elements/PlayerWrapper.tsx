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
    maxBufferLength: 30,
    maxBufferSize: 10 * 1000 * 1000,
    maxBufferHole: 0.5,
    lowLatencyMode: true,
    highBufferWatchdogPeriod: 1,
    nudgeOffset: 0.1,
    nudgeMaxRetry: 5,
    maxFragLookUpTolerance: 0.1,
    liveSyncDurationCount: 1,
    liveMaxLatencyDurationCount: 2,
    fragLoadingTimeOut: 20000,
    fragLoadingMaxRetry: 2,
    fragLoadingRetryDelay: 1000,
    fragLoadingMaxRetryTimeout: 5000,
    startFragPrefetch: false,
    progressive: true,
    abrEwmaDefaultEstimate: 500000,
    abrEwmaFastLive: 3,
    abrEwmaSlowLive: 9,
};

function onProviderChange(
    provider: MediaProviderAdapter | null,
    nativeEvent: MediaProviderChangeEvent,
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

    const uri = new URL(src);

    return (
        <MediaPlayer
            ref={playerRef}
            onEnd={onEnd}
            title={title}
            src={`/proxy/${uri}`}
            playsInline
            onProviderChange={onProviderChange}
        >
            <MediaProvider />
            <DefaultVideoLayout icons={defaultLayoutIcons} />
        </MediaPlayer>
    );
};

export default PlayerWrapper;
