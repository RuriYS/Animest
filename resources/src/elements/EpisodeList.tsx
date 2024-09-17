import React from 'react';
import { MetaProps, EpisodeProps, SortMode } from 'types';
import { EpisodeCard, SortSelect, Pagination } from '@/elements';
import { styled } from 'twin.macro';

interface EpisodeListProps {
    meta: MetaProps;
    episodes: EpisodeProps[];
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
    onSortChange: (sort: SortMode) => void;
}

const List = styled.div`
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 5px;
`;

const EpisodeList: React.FC<EpisodeListProps> = ({
    meta,
    episodes,
    currentPage,
    totalPages,
    onPageChange,
    onSortChange,
}) => (
    <div className='flex flex-col gap-y-4 p-4 bg-neutral-900 rounded-lg'>
        <div className='flex justify-between'>
            <h1 className='text-lg font-semibold'>Episodes</h1>
            <SortSelect onSortChange={onSortChange} />
        </div>
        <List>
            {episodes.map((episode) => (
                <EpisodeCard
                    key={`${episode.title_id}-${episode.episode_index}`}
                    episode={episode}
                    meta={meta}
                />
            ))}
        </List>
        <Pagination
            currentPage={currentPage}
            totalPages={totalPages}
            onPageChange={onPageChange}
        />
    </div>
);

export default EpisodeList;
