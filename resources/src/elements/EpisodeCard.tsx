import { EpisodeProps, MetaProps } from 'types';
import { Episode, Moment } from '@/elements';
import React from 'react';

const EpisodeCard: React.FC<{
    episode: EpisodeProps;
    meta: MetaProps;
}> = ({ episode, meta }) => (
    <Episode to={`/watch/${meta.id}/episode/${episode.episode_index}`}>
        <img
            src={meta.splash}
            alt={meta.id}
            className='absolute inset-0 w-full h-full object-cover opacity-20'
        />
        <div className='p-2 bg-gradient-to-l to-black from-transparent'>
            <h1>{meta.title}</h1>
            <p>Episode {episode.episode_index}</p>
            <Moment uploadDate={episode.upload_date} />
        </div>
    </Episode>
);

export default EpisodeCard;
