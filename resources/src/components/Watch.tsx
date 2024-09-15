import React, { useEffect, useState, useCallback, useRef } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
    MediaPlayer,
    MediaPlayerInstance,
    MediaProvider,
} from '@vidstack/react';
import {
    PlyrLayout,
    plyrLayoutIcons,
} from '@vidstack/react/player/layouts/plyr';
import styled from '@emotion/styled';
import axios from 'axios';
import moment from 'moment';
import tw from 'twin.macro';
import { Link } from 'react-router-dom';
import { Constraint } from '../elements';
import Button from '../elements/Button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const WatchContainer = styled.div`
    ${tw`flex flex-col gap-y-8 w-full bg-black`}
`;

interface Meta {
    id: string;
    title: string;
    language: 'sub' | 'dub';
    length: string | number;
    description: string;
    names: string;
    episodes: number;
    season: string;
    splash: string;
    status: string;
    year: string;
}

interface Source {
    file: string;
}

interface Episode {
    title_id: string;
    episode_index: string;
    upload_date: string;
    video: {
        source: Source[];
        source_bk: Source[];
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

const EpisodeList = styled.div`
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 5px;
`;

const Episode = styled(Link)`
    ${tw`flex gap-x-4 p-2 bg-neutral-800 hover:bg-neutral-300 rounded-lg`}

    &:hover h1,
    &:hover p {
        filter: invert(100%);
    }

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

const Watch: React.FC = () => {
    const { id, episodeId } = useParams<{ id: string; episodeId: string }>();
    const navigate = useNavigate();
    const playerRef = useRef<MediaPlayerInstance>(null);
    const [loading, setLoading] = useState<boolean>(true);
    const [meta, setMeta] = useState<Meta | null>(null);
    const [episodes, setEpisodes] = useState<Episode[] | null>(null);
    const [sortedEpisodes, setSortedEpisodes] = useState<Episode[] | null>(
        null,
    );
    const [episode, setEpisode] = useState<Episode | undefined>(undefined);
    const [views, setViews] = useState<number | undefined>(0);
    const [currentPage, setCurrentPage] = useState(1);
    const [sortMode, setSort] = useState<
        'newest' | 'oldest' | 'index-asc' | 'index-desc'
    >('newest');
    const episodesPerPage = 10;

    const sortEpisodes = useCallback(
        (eps: Episode[] | null, mode: typeof sortMode) => {
            if (!eps) return null;
            const sorted = [...eps];
            switch (mode) {
                case 'newest':
                    return sorted.sort(
                        (a, b) =>
                            moment(b.upload_date).valueOf() -
                            moment(a.upload_date).valueOf(),
                    );
                case 'oldest':
                    return sorted.sort(
                        (a, b) =>
                            moment(a.upload_date).valueOf() -
                            moment(b.upload_date).valueOf(),
                    );
                case 'index-asc':
                    return sorted.sort(
                        (a, b) =>
                            parseInt(a.episode_index) -
                            parseInt(b.episode_index),
                    );
                case 'index-desc':
                    return sorted.sort(
                        (a, b) =>
                            parseInt(b.episode_index) -
                            parseInt(a.episode_index),
                    );
                default:
                    return sorted;
            }
        },
        [],
    );

    const fetchEpisodes = useCallback(async () => {
        try {
            const { data } = await axios.get(`/api/videos/${id}`);
            if (!data.exists) {
                setTimeout(fetchEpisodes, 5000);
                return;
            }
            setEpisodes(data.episodes);
            setSortedEpisodes(sortEpisodes(data.episodes, sortMode));
            setLoading(false);
        } catch (error) {
            console.error('Error fetching episodes:', error);
            setTimeout(fetchEpisodes, 5000);
        }
    }, [id, sortEpisodes]);

    const fetchMeta = useCallback(async () => {
        try {
            const { data } = await axios.get(`/api/titles/${id}`);
            if (!data.result) {
                setTimeout(fetchMeta, 5000);
                return;
            }
            setMeta(data.result);
        } catch (error) {
            console.error('Error fetching meta:', error);
            setTimeout(fetchMeta, 5000);
        }
    }, [id]);

    useEffect(() => {
        fetchEpisodes();
        fetchMeta();
    }, [fetchEpisodes, fetchMeta]);

    useEffect(() => {
        if (episodes) {
            setSortedEpisodes(sortEpisodes(episodes, sortMode));
        }
    }, [episodes, sortMode, sortEpisodes]);

    useEffect(() => {
        if (sortedEpisodes) {
            setEpisode(
                sortedEpisodes.find((ep) => ep.episode_index === episodeId),
            );
        }
    }, [sortedEpisodes, episodeId]);

    useEffect(() => {
        if (episode?.views) {
            setViews(episode.views);
        }
    }, [episode]);

    const handleEpisodeEnd = () => {
        const nextEpisode = episodes?.find(
            (ep) =>
                parseInt(ep.episode_index) ===
                parseInt(episode?.episode_index!) + 1,
        );

        if (nextEpisode) {
            navigate(`/watch/${id}/episode/${nextEpisode.episode_index}`);
        }
    };

    useEffect(() => {
        if (playerRef.current) {
            const player = playerRef.current;
            player.addEventListener('ended', handleEpisodeEnd);
            return () => {
                player.removeEventListener('ended', handleEpisodeEnd);
            };
        }
    }, [handleEpisodeEnd]);

    const indexOfLastEpisode = currentPage * episodesPerPage;
    const indexOfFirstEpisode = indexOfLastEpisode - episodesPerPage;
    const currentEpisodes = sortedEpisodes?.slice(
        indexOfFirstEpisode,
        indexOfLastEpisode,
    );

    const totalPages = sortedEpisodes
        ? Math.ceil(sortedEpisodes.length / episodesPerPage)
        : 0;

    const paginate = (pageNumber: number) => setCurrentPage(pageNumber);

    if (loading) return <div>Loading...</div>;

    return (
        <Constraint>
            <WatchContainer>
                {episode && (
                    <MediaPlayer
                        ref={playerRef}
                        onEnd={handleEpisodeEnd}
                        title={meta?.title}
                        src={episode.video.source[0].file}
                        playsInline
                    >
                        <MediaProvider />
                        <PlyrLayout icons={plyrLayoutIcons} />
                    </MediaPlayer>
                )}
                {meta && (
                    <div className='grid px-2 gap-2 lg:grid-cols-2 lg:px-8 lg:gap-4'>
                        <div className='flex flex-col gap-y-4'>
                            <div className='px-2'>
                                <h1 className='text-2xl font-semibold'>
                                    {meta.title}
                                </h1>
                                <h2 className='font-semibold'>
                                    Episode {episode?.episode_index}
                                    {meta.language === 'sub'
                                        ? ' (Subbed)'
                                        : ' (Dubbed)'}
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
                                <p className='text-sm text-gray-500'>
                                    {meta.names}
                                </p>
                                <p className='text-xs text-gray-500'>
                                    {meta.description}
                                </p>
                            </div>
                        </div>
                        <div className='flex flex-col gap-y-4 p-4 bg-neutral-900 rounded-lg'>
                            <div className='flex justify-between'>
                                <h1 className='text-lg font-semibold'>
                                    Episodes
                                </h1>
                                <Select
                                    onValueChange={(value: typeof sortMode) =>
                                        setSort(value)
                                    }
                                >
                                    <SelectTrigger className='w-[180px] outline-none border-[1px] border-gray-500 hover:border-gray-300 focus:ring-1 focus:ring-gray-400 transition-colors'>
                                        <SelectValue placeholder='Sort' />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value='newest'>
                                            Newest First
                                        </SelectItem>
                                        <SelectItem value='oldest'>
                                            Oldest First
                                        </SelectItem>
                                        <SelectItem value='index-asc'>
                                            Episode (Ascending)
                                        </SelectItem>
                                        <SelectItem value='index-desc'>
                                            Episode (Descending)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className='flex justify-between items-center mt-4'>
                                <Button
                                    variant='primary'
                                    onClick={() => paginate(currentPage - 1)}
                                    disabled={currentPage === 1}
                                >
                                    Previous
                                </Button>
                                <span className='text-xs'>{`Page ${currentPage} of ${totalPages}`}</span>
                                <Button
                                    variant='secondary'
                                    onClick={() => paginate(currentPage + 1)}
                                    disabled={currentPage === totalPages}
                                >
                                    Next
                                </Button>
                            </div>
                            <EpisodeList>
                                {currentEpisodes?.map((episode) => (
                                    <EpisodeCard
                                        key={`${episode.title_id}-${episode.episode_index}`}
                                        episode={episode}
                                        meta={meta}
                                    />
                                ))}
                            </EpisodeList>
                        </div>
                    </div>
                )}
            </WatchContainer>
        </Constraint>
    );
};

export default Watch;
