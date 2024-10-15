import axios from 'axios';
import { useEffect, useRef, useState } from 'react';
import { EpisodeProps, MetaProps as Title } from 'types';

interface EpisodeState {
    loading: boolean;
    title: Title | null;
    episodes: EpisodeProps[] | null;
    error: Error | string | null;
    header: string;
    message: string;
}

export default function useEpisodeData(title_id: string) {
    const pollIntervalRef = useRef<NodeJS.Timeout>();
    const [state, setState] = useState<EpisodeState>({
        loading: true,
        title: null,
        episodes: null,
        error: '',
        header: '',
        message: '',
    });

    const fetchData = async () => {
        try {
            const titleResponse = await axios.get(`/api/titles/${title_id}`);
            if (titleResponse.data.message.result) {
                const title = titleResponse.data.message.result;
                setState((prev) => ({ ...prev, title }));

                const episodesResponse = await axios.get(
                    `/api/titles/${title_id}/episodes`,
                );

                switch (episodesResponse.status) {
                    case 202:
                        setState((prev) => ({
                            ...prev,
                            message: 'Episodes currently in queue',
                            loading: true,
                        }));
                        return true;
                    case 200:
                        const episodes: EpisodeProps[] =
                            episodesResponse.data.message.result.data;

                        setState((prev) => ({
                            ...prev,
                            episodes,
                            loading: false,
                        }));
                        return true;
                    default:
                        setState((prev) => ({
                            ...prev,
                            header: 'Error',
                            message: 'Unexpected error',
                            loading: false,
                        }));
                        return true;
                }
            }
        } catch (error: any) {
            setState((prev) => ({
                ...prev,
                error:
                    error instanceof Error
                        ? error.message
                        : 'An error occurred',
                loading: false,
                header: 'Error',
                message: error.message,
            }));
            return true;
        }
    };

    const pollData = async () => {
        const success = await fetchData();
        if (!success) {
            pollIntervalRef.current = setTimeout(pollData, 5000);
        }
    };

    useEffect(() => {
        pollData();

        return () => {
            if (pollIntervalRef.current) {
                clearTimeout(pollIntervalRef.current);
            }
        };
    }, [title_id]);

    useEffect(() => {
        console.debug('useEpisode', state);
    }, [state]);
    return state;
}
