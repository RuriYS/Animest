import React, { useState } from 'react';
import { MetaProps, EpisodeProps } from 'types';
import { Moment } from '@/elements';

interface EpisodeInfoProps {
    meta: MetaProps;
    episode: EpisodeProps;
    views: number;
}

const EpisodeInfo: React.FC<EpisodeInfoProps> = ({
    meta,
    episode,
    views = 0,
}) => {
    const [expanded, setExpanded] = useState(false);

    return (
        <div className='flex flex-col gap-y-4'>
            <div className='flex-1 flex-col lg:flex gap-4 p-4 bg-neutral-900 rounded-lg'>
                <div className='flex gap-2 mb-4'>
                    <p className='flex gap-1 text-xs'>
                        <span>{views <= 0 ? 'No' : views}</span>
                        <span className='muted'>
                            view{views > 1 || views == 0 ? 's' : ''}
                        </span>
                    </p>
                    <Moment
                        uploadDate={episode?.upload_date}
                        className='text-xs'
                    />
                </div>
                <p className='text-xs text-muted-foreground'>{meta.names}</p>
                <p
                    className={`text-xs text-muted-foreground ${
                        expanded ? '' : 'line-clamp-2'
                    }`}
                >
                    {meta.description}
                </p>
                <button
                    className='text-xs'
                    type='button'
                    onClick={() => setExpanded(!expanded)}
                >
                    {expanded ? 'Read less' : 'Read more'}
                </button>
            </div>
        </div>
    );
};

export default EpisodeInfo;
