import { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';
import { EpisodeProps, MetaProps } from 'types';
import { URLSearchParams } from 'url';
import { error } from 'console';

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

export default function useEpisodes(id: string, args?: URLSearchParams) {
    const [state, setState] = useState({
        loading: true,
        episodes: null as EpisodeProps[] | null,
        meta: null as MetaProps | null,
        error: null as Error | null,
        message: null as string | null,
    });
    const [episodesPolling, setEpisodesPolling] = useState(true);
    const [metaPolling, setMetaPolling] = useState(true);

    const fetchEpisodes = useCallback(async () => {
        try {
            const query = args ? `?${args.toString()}` : '';
            const { data, status } = await axios.get(
                `/api/episodes/${id}${query}`,
            );
            if (data.exists) {
                setState((prev) => ({
                    ...prev,
                    episodes: data.episodes,
                    loading: false,
                }));
                setEpisodesPolling(false);
            } else if (data.errors) {
                throw new Error(data.errors);
            } else if (status === 202) {
                setState((prev) => ({
                    ...prev,
                    message: data.message,
                }));
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
            const { data, status } = await axios.get(`/api/titles/${id}`);
            if (data.result) {
                setState((prev) => ({ ...prev, meta: data.result }));
                setMetaPolling(false);
            } else if (data.errors) {
                throw new Error(data.errors);
            } else if (status === 202) {
                setState((prev) => ({
                    ...prev,
                    message: data.message,
                }));
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
