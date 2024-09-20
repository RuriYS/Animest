import { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';
import { EpisodeProps, MetaProps } from 'types';

function useInterval(callback: () => void, delay: number | null) {
    const savedCallback = useRef<() => void>();

    useEffect(() => {
        savedCallback.current = callback;
    }, [callback]);

    useEffect(() => {
        function tick() {
            savedCallback.current?.();
        }
        if (delay !== null) {
            const id = setInterval(tick, delay);
            return () => clearInterval(id);
        }
    }, [delay]);
}

export default function useEpisodesAndMeta(id: string) {
    const [state, setState] = useState({
        loading: true,
        episodes: null as EpisodeProps[] | null,
        meta: null as MetaProps | null,
        error: null as Error | null,
    });
    const [episodesPolling, setEpisodesPolling] = useState(true);
    const [metaPolling, setMetaPolling] = useState(true);

    const fetchEpisodes = useCallback(async () => {
        try {
            const { data } = await axios.get(`/api/videos/${id}`);
            if (data.exists) {
                setState((prev) => ({
                    ...prev,
                    episodes: data.episodes,
                    loading: false,
                }));
                setEpisodesPolling(false);
            }
        } catch (error) {
            setState((prev) => ({
                ...prev,
                error: error as Error,
                loading: false,
            }));
            setEpisodesPolling(false);
            setMetaPolling(false);
        }
    }, [id]);

    const fetchMeta = useCallback(async () => {
        try {
            const { data } = await axios.get(`/api/titles/${id}`);
            if (data.result) {
                setState((prev) => ({ ...prev, meta: data.result }));
                setMetaPolling(false);
            } else if (data.errors) {
                throw new Error(data.errors);
            }
        } catch (error) {
            setState((prev) => ({
                ...prev,
                error: error as Error,
                loading: false,
            }));
            setEpisodesPolling(false);
            setMetaPolling(false);
        }
    }, [id]);

    useInterval(fetchEpisodes, episodesPolling ? 5000 : null);
    useInterval(fetchMeta, metaPolling ? 5000 : null);

    useEffect(() => {
        fetchMeta();
        fetchEpisodes();
    }, [fetchEpisodes, fetchMeta]);

    return state;
}
