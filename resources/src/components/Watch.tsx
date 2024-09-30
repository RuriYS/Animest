import React, { useState, useMemo, useCallback, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import styled from '@emotion/styled';
import tw from 'twin.macro';
import {
    Constraint,
    EpisodeInfo,
    EpisodeList,
    PlayerWrapper,
    WatchInfoContainer,
} from '@/elements';
import moment from 'moment';
import { EpisodeProps, SortMode } from 'types';
import useTitle from '@/hooks/useTitle';

const WatchContainer = styled.div`
    ${tw`flex flex-col gap-y-8 w-full bg-black`}
`;

export default function Watch() {
    const navigate = useNavigate();
    const { id, index } = useParams<{
        id: string;
        index?: string;
    }>();
    const {
        loading,
        episodes,
        title: meta,
        error,
        header,
        message,
    } = useTitle(id!);
    const [currentPage, setCurrentPage] = useState(1);
    const [sortMode, setSort] = useState<SortMode>('index-desc');
    const [currentEpisode, setCurrentEpisode] = useState<EpisodeProps | null>(
        null,
    );
    const episodesPerPage = 10;

    useEffect(() => {
        if (!index) {
            navigate(`/watch/${id}/episode/1`, { replace: true });
        }
    }, [id, index, navigate]);

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

    useEffect(() => {
        if (episodes) {
            const episode =
                episodes?.find(
                    (episode) => episode.episode_index === (index ?? '1'),
                ) ?? episodes[0];
            return setCurrentEpisode(episode);
        }
    }, [episodes, index]);

    if (loading || !sortedEpisodes || !currentEpisode) {
        return (
            <WatchInfoContainer
                state={error ? 'error' : 'loading'}
                header={header}
                message={message}
            />
        );
    }

    const indexOfLastEpisode = currentPage * episodesPerPage;
    const indexOfFirstEpisode = indexOfLastEpisode - episodesPerPage;
    const currentEpisodes = sortedEpisodes!.slice(
        indexOfFirstEpisode,
        indexOfLastEpisode,
    );
    const totalPages = Math.ceil(sortedEpisodes!.length / episodesPerPage);

    return (
        <Constraint>
            <title>{meta!.title} | Animest</title>
            {currentEpisode && (
                <WatchContainer>
                    <PlayerWrapper
                        src={currentEpisode!.video.source[0].file}
                        title={`${meta!.title} · Episode ${
                            currentEpisode!.episode_index
                        }`}
                        onEnd={handleEpisodeEnd}
                    />
                    <div className='grid px-2 gap-2 lg:grid-cols-2 lg:px-8 lg:gap-4'>
                        <EpisodeInfo
                            meta={meta!}
                            episode={currentEpisode!}
                            views={currentEpisode!.views}
                        />
                        <EpisodeList
                            meta={meta!}
                            episodes={currentEpisodes}
                            currentPage={currentPage}
                            totalPages={totalPages}
                            onPageChange={handlePageChange}
                            onSortChange={handleSortChange}
                        />
                    </div>
                </WatchContainer>
            )}
        </Constraint>
    );
}
