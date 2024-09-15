import { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import { EpisodeProps, MetaProps } from 'types';

export default (id: string) => {
    const [loading, setLoading] = useState(true);
    const [episodes, setEpisodes] = useState<EpisodeProps[] | null>(null);
    const [meta, setMeta] = useState<MetaProps | null>(null);
    const [error, setError] = useState<Error | null>(null);

    const fetchEpisodes = useCallback(async () => {
        try {
            const { data } = await axios.get(`/api/videos/${id}`);
            if (!data.exists) {
                setTimeout(fetchEpisodes, 5000);
                return;
            }
            setEpisodes(data.episodes);
            setLoading(false);
        } catch (error) {
            setError(error as Error);
            setTimeout(fetchEpisodes, 5000);
        }
    }, [id]);

    const fetchMeta = useCallback(async () => {
        try {
            const { data } = await axios.get(`/api/titles/${id}`);
            if (!data.result) {
                setTimeout(fetchMeta, 5000);
                return;
            }
            setMeta(data.result);
        } catch (error) {
            setError(error as Error);
            setTimeout(fetchMeta, 5000);
        }
    }, [id]);

    useEffect(() => {
        fetchEpisodes();
        fetchMeta();
    }, [fetchEpisodes, fetchMeta]);

    return { loading, episodes, meta, error };
};
