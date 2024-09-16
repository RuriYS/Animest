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
    const [loading, setLoading] = useState(true);
    const [episodes, setEpisodes] = useState<EpisodeProps[] | null>(null);
    const [meta, setMeta] = useState<MetaProps | null>(null);
    const [error, setError] = useState<Error | null>(null);
    const [episodesPolling, setEpisodesPolling] = useState(true);
    const [metaPolling, setMetaPolling] = useState(true);

    const fetchEpisodes = useCallback(async () => {
        try {
            const { data } = await axios.get(`/api/videos/${id}`);
            if (data.exists) {
                setEpisodes(data.episodes);
                setLoading(false);
                setEpisodesPolling(false);
            }
        } catch (error) {
            setError(error as Error);
        }
    }, [id]);

    const fetchMeta = useCallback(async () => {
        try {
            const { data } = await axios.get(`/api/titles/${id}`);
            if (data.result) {
                setMeta(data.result);
                setMetaPolling(false);
            }
        } catch (error) {
            setError(error as Error);
        }
    }, [id]);

    useInterval(fetchEpisodes, episodesPolling ? 5000 : null);
    useInterval(fetchMeta, metaPolling ? 5000 : null);

    useEffect(() => {
        fetchEpisodes();
        fetchMeta();
    }, [fetchEpisodes, fetchMeta]);

    return { loading, episodes, meta, error };
}
