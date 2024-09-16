export interface MetaProps {
    id: string;
    title: string;
    language: 'SUB' | 'DUB';
    length: string | number;
    description: string;
    names: string;
    episodes: number;
    season: string;
    splash: string;
    status: string;
    year: string;
}

export interface SourceProps {
    file: string;
}

export interface EpisodeProps {
    title_id: string;
    episode_index: string;
    upload_date: string;
    video: {
        source: SourceProps[];
        source_bk: SourceProps[];
    };
    views: number;
}

export type SortMode = 'newest' | 'oldest' | 'index-asc' | 'index-desc';
