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
                if (episodesResponse.data.message.result) {
                    const episodes: EpisodeProps[] =
                        episodesResponse.data.message.result.data;

                    setState((prev) => ({
                        ...prev,
                        episodes,
                        loading: false,
                    }));
                    return true;
                }
            }
            return false;
        } catch (error) {
            setState((prev) => ({
                ...prev,
                error:
                    error instanceof Error
                        ? error.message
                        : 'An error occurred',
                loading: false,
            }));
            return false;
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

    return state;
}
