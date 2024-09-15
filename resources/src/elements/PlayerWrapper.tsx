import React, { useEffect, useRef } from 'react';
import {
    MediaPlayer,
    MediaPlayerInstance,
    MediaProvider,
} from '@vidstack/react';
import {
    PlyrLayout,
    plyrLayoutIcons,
} from '@vidstack/react/player/layouts/plyr';

interface MediaPlayerWrapperProps {
    src: string;
    title: string;
    onEnd: () => void;
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

    return (
        <MediaPlayer
            ref={playerRef}
            onEnd={onEnd}
            title={title}
            src={src}
            playsInline
        >
            <MediaProvider />
            <PlyrLayout icons={plyrLayoutIcons} />
        </MediaPlayer>
    );
};

export default PlayerWrapper;
