import React from 'react';
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
}) => (
    <div className='flex flex-col gap-y-4'>
        <div className='px-2'>
            <h1 className='text-2xl font-semibold'>{meta.title}</h1>
            <h2 className='font-semibold'>
                Episode {episode?.episode_index}
                {meta.language && ` (${meta.language[0].toUpperCase()}ubbed)`}
            </h2>
        </div>
        <div className='flex-1 flex-col lg:flex gap-4 p-4 bg-neutral-900 rounded-lg'>
            <div className='flex gap-2'>
                <p className='flex gap-1 text-sm'>
                    <span>{views <= 0 ? 'No' : views}</span>
                    <span className='muted'>
                        view{views > 1 || views == 0 ? 's' : ''}
                    </span>
                </p>
                <Moment uploadDate={episode?.upload_date} className='text-sm' />
            </div>
            <p className='text-sm muted'>{meta.names}</p>
            <p className='text-xs muted'>{meta.description}</p>
        </div>
    </div>
);

export default EpisodeInfo;
