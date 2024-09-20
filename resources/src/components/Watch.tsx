import React, { useState, useMemo, useCallback, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import styled from '@emotion/styled';
import tw from 'twin.macro';
import {
    Constraint,
    ContentContainer,
    EpisodeInfo,
    EpisodeList,
    PlayerWrapper,
} from '@/elements';
import useEpisodes from '@/hooks/useEpisodes';
import moment from 'moment';
import { SortMode } from 'types';
import { WatchLoading } from '@/elements';

const WatchContainer = styled.div`
    ${tw`flex flex-col gap-y-8 w-full bg-black`}
`;

export default function Watch() {
    const navigate = useNavigate();
    const { id, episodeIndex } = useParams<{
        id: string;
        episodeIndex?: string;
    }>();
    const { loading, episodes, meta, error } = useEpisodes(id!);
    const [currentPage, setCurrentPage] = useState(1);
    const [sortMode, setSort] = useState<SortMode>('index-asc');
    const episodesPerPage = 10;

    useEffect(() => {
        if (!episodeIndex) {
            navigate(`/watch/${id}/episode/1`);
        }
    }, [id, episodeIndex, navigate]);

    const sortedEpisodes = useMemo(() => {
        if (!episodes) return null;
        const sorted = [...episodes];
        switch (sortMode) {
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
                        parseInt(a.episode_index) - parseInt(b.episode_index),
                );
            case 'index-desc':
                return sorted.sort(
                    (a, b) =>
                        parseInt(b.episode_index) - parseInt(a.episode_index),
                );
            default:
                return sorted;
        }
    }, [episodes, sortMode]);

    const currentEpisode = useMemo(
        () => sortedEpisodes?.find((ep) => ep.episode_index === episodeIndex),
        [sortedEpisodes, episodeIndex],
    );

    const handleEpisodeEnd = useCallback(() => {
        const nextEpisode = sortedEpisodes?.find(
            (ep) =>
                parseInt(ep.episode_index) ===
                parseInt(currentEpisode?.episode_index!) + 1,
        );

        if (nextEpisode) {
            navigate(`/watch/${id}/episode/${nextEpisode.episode_index}`);
        }
    }, [sortedEpisodes, currentEpisode, navigate, id]);

    const handlePageChange = useCallback((page: number) => {
        setCurrentPage(page);
    }, []);

    const handleSortChange = useCallback((sort: typeof sortMode) => {
        setSort(sort);
    }, []);

    if (loading) return <WatchLoading />;
    if (error)
        return (
            <ContentContainer>
                <Constraint>
                    <div className='flex flex-col gap-4'>
                        <h1 className='text-lg'>{`The resource doesn't seem to exist :(`}</h1>
                        <span>
                            <p className='muted'>
                                If you think that this is a mistake, please
                                contact the developers.
                            </p>
                            <p className='muted'>
                                Error message: "{error.message}"
                            </p>
                        </span>
                    </div>
                </Constraint>
            </ContentContainer>
        );
    if (!meta || !sortedEpisodes || !currentEpisode) return null;

    const indexOfLastEpisode = currentPage * episodesPerPage;
    const indexOfFirstEpisode = indexOfLastEpisode - episodesPerPage;
    const currentEpisodes = sortedEpisodes.slice(
        indexOfFirstEpisode,
        indexOfLastEpisode,
    );
    const totalPages = Math.ceil(sortedEpisodes.length / episodesPerPage);

    return (
        <Constraint>
            <WatchContainer>
                <PlayerWrapper
                    src={currentEpisode.video.source[0].file}
                    title={meta.title}
                    onEnd={handleEpisodeEnd}
                />
                <div className='grid px-2 gap-2 lg:grid-cols-2 lg:px-8 lg:gap-4'>
                    <EpisodeInfo
                        meta={meta}
                        episode={currentEpisode}
                        views={currentEpisode.views}
                    />
                    <EpisodeList
                        meta={meta}
                        episodes={currentEpisodes}
                        currentPage={currentPage}
                        totalPages={totalPages}
                        onPageChange={handlePageChange}
                        onSortChange={handleSortChange}
                    />
                </div>
            </WatchContainer>
        </Constraint>
    );
}
