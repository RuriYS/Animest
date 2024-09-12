import { MediaPlayer, MediaProvider } from '@vidstack/react';
import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import {
    PlyrLayout,
    plyrLayoutIcons,
} from '@vidstack/react/player/layouts/plyr';
import { Constraint } from '../elements';
import { Link } from 'react-router-dom';
import styled from '@emotion/styled';
import axios from 'axios';
import moment from 'moment';
import tw from 'twin.macro';

const WatchContainer = styled.div`
    ${tw`flex flex-col gap-y-8 w-full bg-black`}
`;

interface Meta {
    id: string;
    title: string;
    description: string;
    episodes: number;
    splash: string;
}

interface Source {
    file: string;
}

interface Episode {
    title_id: string;
    episode_index: string;
    upload_date: string;
    video: {
        stream_data: {
            source: Source[];
            source_bk: Source[];
        };
    };
    views: number;
}

const Moment: React.FC<{ uploadDate?: string }> = ({ uploadDate }) => {
    const [dateNum, dateSuffix] = getMoment(uploadDate);
    return (
        <p className='flex gap-1 text-sm'>
            <span>{dateNum}</span>
            <span className='text-gray-300'>{dateSuffix}</span>
        </p>
    );
};

const getMoment = (uploadDate?: string): [string, string] => {
    if (!uploadDate) return ['', ''];
    const [dateNum, ...dateSuffixes] = moment(uploadDate).fromNow().split(' ');
    const suffix = dateSuffixes.join(' ');
    return [dateNum, suffix];
};

const EpisodeCard: React.FC<{
    episode: Episode;
    meta: Meta;
}> = ({ episode, meta }) => (
    <Episode to={`/watch/${meta.id}/episode/${episode.episode_index}`}>
        <img src={meta.splash} alt={meta.id} />
        <div>
            <h1>{meta.title}</h1>
            <p>Episode {episode.episode_index}</p>
            <Moment uploadDate={episode.upload_date} />
        </div>
    </Episode>
);

const Watch: React.FC = () => {
    const { id, episodeId } = useParams<{ id: string; episodeId: string }>();
    const [loading, setLoading] = useState<boolean>(true);
    const [meta, setMeta] = useState<Meta | null>(null);
    const [episodes, setEpisodes] = useState<Episode[] | null>(null);
    const [episode, setEpisode] = useState<Episode | undefined>(undefined);
    const [views, setViews] = useState<number | undefined>(0);

    useEffect(() => {
        const fetchData = async () => {
            const { data } = await axios.get(`/api/videos/${id}`);
            setEpisodes(
                data.episodes.sort((a: Episode, b: Episode) =>
                    moment(b.upload_date).diff(moment(a.upload_date)),
                ),
            );
            setLoading(false);
        };
        fetchData();
    }, []);

    useEffect(() => {
        const getMeta = async () => {
            const anime = await axios.get(`/api/animes/${id}`);
            setMeta(anime.data);
        };
        if (episodes) {
            getMeta();
            setEpisode(episodes.find((ep) => ep.episode_index === episodeId));
        }
    }, [episodes, episodeId]);

    if (loading) return <div></div>;

    return (
        <Constraint>
            <WatchContainer>
                {episode && (
                    <MediaPlayer
                        title={meta?.title}
                        src={episode?.video.stream_data.source[0].file}
                        playsInline
                    >
                        <MediaProvider />
                        <PlyrLayout icons={plyrLayoutIcons} />
                    </MediaPlayer>
                )}
                {meta && (
                    <div className='grid px-2 gap-2 lg:grid-cols-2 lg:px-8 lg:gap-8'>
                        <div className='flex flex-col gap-y-4'>
                            <div>
                                <h1 className='text-2xl font-semibold'>
                                    {meta.title}
                                </h1>
                                <h2 className='font-semibold'>
                                    Episode {episode?.episode_index}
                                </h2>
                            </div>
                            <div className='flex-1 flex-col lg:flex gap-4 p-4 bg-neutral-900 rounded-lg'>
                                <div className='flex gap-4'>
                                    <p className='flex gap-1 text-sm'>
                                        <span>{views}</span>
                                        <span className='text-gray-300'>
                                            views
                                        </span>
                                    </p>
                                    <Moment uploadDate={episode?.upload_date} />
                                </div>
                                <p className='text-xs text-gray-500'>
                                    {meta.description}
                                </p>
                            </div>
                        </div>
                        <div className='flex flex-col gap-y-4 p-4 bg-neutral-900 rounded-lg'>
                            <h1 className='text-lg font-semibold'>Episodes</h1>
                            <ul>
                                {episodes?.map((episode) => (
                                    <EpisodeCard
                                        key={`${episode.title_id}-${episode.episode_index}`}
                                        episode={episode}
                                        meta={meta}
                                    />
                                ))}
                            </ul>
                        </div>
                    </div>
                )}
            </WatchContainer>
        </Constraint>
    );
};

const Episode = styled(Link)`
    ${tw`flex gap-x-4 p-2 hover:bg-gray-800 rounded-lg`}

    img {
        ${tw`rounded-lg`}
        width: 50px;
    }

    p {
        font-size: 0.85rem;
    }

    span {
        font-size: 0.7rem;
    }
`;

export default Watch;
