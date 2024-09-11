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
    title: string;
    date_added: string;
    splash: string;
}

interface Source {
    file: string;
}

interface Video {
    stream_data: {
        source: Source[];
        source_bk: Source[];
    };
}

const Watch: React.FC = () => {
    const { id, episodeId } = useParams<{ id: string; episodeId: string }>();
    const [loading, setLoading] = useState<boolean>(true);
    const [meta, setMeta] = useState<Meta | null>();
    const [video, setVideo] = useState<Video | null>();

    useEffect(() => {
        const fetchStreamData = async () => {
            try {
                const { data } = await axios.get(
                    `/api/videos/${id}/episode-${episodeId}`,
                );
                const { meta, video } = data;
                setMeta(meta);
                setVideo(video);
                setLoading(false);
            } catch (error) {
                console.error('Failed to fetch stream data:', error);
                setLoading(false);
            }
        };

        fetchStreamData();
    }, [id, episodeId]);

    const date = meta?.date_added.toString();
    const [dateNum, ...dateSuffixes] = moment(date).fromNow().split(' ');

    return (
        <Constraint>
            <WatchContainer>
                <MediaPlayer
                    title={meta?.title}
                    src={video?.stream_data.source[0].file}
                    playsInline
                >
                    <MediaProvider />
                    <PlyrLayout icons={plyrLayoutIcons} />
                </MediaPlayer>
                <div className='grid px-2 gap-2 lg:grid-cols-2 lg:px-8 lg:gap-8'>
                    <div className='flex flex-col gap-y-4'>
                        <h1 className='text-2xl font-semibold'>
                            {meta?.title}
                        </h1>
                        <div className='hidden flex-1 lg:flex gap-4 p-4 bg-neutral-900 rounded-lg'>
                            <p className='flex gap-1 text-sm'>
                                <span>69420</span>
                                <span className='text-gray-300'>views</span>
                            </p>
                            <p className='flex gap-1 text-sm'>
                                <span>{dateNum}</span>
                                <span className='text-gray-300'>
                                    {dateSuffixes.join(' ')}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div className='flex flex-col gap-y-4 p-4 bg-neutral-900 rounded-lg'>
                        <div className='lg:hidden flex gap-4 bg-neutral-900 rounded-lg'>
                            <p className='flex gap-1 text-sm'>
                                <span>69420</span>
                                <span className='text-gray-300'>views</span>
                            </p>
                            <p className='flex gap-1 text-sm'>
                                <span>{dateNum}</span>
                                <span className='text-gray-300'>
                                    {dateSuffixes.join(' ')}
                                </span>
                            </p>
                        </div>
                        <h1 className='text-lg font-semibold'>Episodes</h1>
                        <ul>
                            <Episode
                                to={
                                    '/watch/shikanoko-nokonoko-koshitantan/episode/1'
                                }
                            >
                                <img
                                    src='https://gogocdn.net/cover/shikanoko-nokonoko-koshitantan.png'
                                    alt='Shikanoko Nokonoko Koshitantan - Episode 1'
                                />
                                <div className='flex flex-col gap-y-2'>
                                    <p>Episode 1</p>
                                    <span>2 months ago</span>
                                </div>
                            </Episode>
                        </ul>
                    </div>
                </div>
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
