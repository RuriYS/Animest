import { useState, useEffect, useCallback, useRef } from 'react';
import axios, { CancelTokenSource } from 'axios';
import { EpisodeProps, MetaProps } from 'types';

const MAX_POLLING_ATTEMPTS = 20;
const POLLING_INTERVAL = 5000;
const TIMEOUT_DURATION = 60000;

interface EpisodesState {
    loading: boolean;
    episodes: EpisodeProps[] | null;
    currentEpisode: EpisodeProps | null | undefined;
    meta: MetaProps | null;
    error: Error | string | null;
    header: string;
    message: string;
}

export default function useEpisodes(
    id: string,
    index: string | undefined,
    args?: URLSearchParams,
) {
    const [state, setState] = useState<EpisodesState>({
        loading: true,
        episodes: null,
        currentEpisode: null,
        meta: null,
        error: null,
        header: 'Loading episode',
        message: 'Initializing',
    });
    const [episodesPolling, setEpisodesPolling] = useState(true);
    const [metaPolling, setMetaPolling] = useState(true);
    const pollingRef = useRef(true);
    const pollingAttemptsRef = useRef(0);
    const cancelSourceRef = useRef<CancelTokenSource | null>(null);

    const createCancelToken = useCallback(() => {
        cancelSourceRef.current = axios.CancelToken.source();
        return cancelSourceRef.current.token;
    }, []);

    const fetchEpisodes = useCallback(async () => {
        try {
            setState((prev) => ({ ...prev, message: 'Fetching episodes' }));
            const query = args ? `?${args.toString()}` : '';
            const { data, status } = await axios.get(
                `/api/episodes/${id}${query}`,
                {
                    cancelToken: createCancelToken(),
                    timeout: TIMEOUT_DURATION,
                },
            );

            if (data.exists) {
                setState((prev) => ({ ...prev, episodes: data.episodes }));
                setEpisodesPolling(false);
            } else if (data.errors) {
                throw new Error(data.errors);
            } else if (status === 202) {
                setState((prev) => ({ ...prev, message: data.message }));
                pollingAttemptsRef.current++;
            }
        } catch (error: any) {
            if (axios.isCancel(error)) {
                console.log('Request canceled:', error.message);
            } else {
                handleError(error);
            }
        }
    }, [id, args, createCancelToken]);

    const fetchMeta = useCallback(async () => {
        try {
            setState((prev) => ({ ...prev, message: 'Fetching metadata' }));
            const { data, status } = await axios.get(`/api/titles/${id}`, {
                cancelToken: createCancelToken(),
                timeout: TIMEOUT_DURATION,
            });

            if (data.result) {
                setState((prev) => ({ ...prev, meta: data.result }));
                setMetaPolling(false);
            } else if (data.errors) {
                throw new Error(data.errors);
            } else if (status === 202) {
                setState((prev) => ({ ...prev, message: data.message }));
                pollingAttemptsRef.current++;
            }
        } catch (error: any) {
            if (axios.isCancel(error)) {
                console.log('Request canceled:', error.message);
            } else {
                handleError(error);
            }
        }
    }, [id, createCancelToken]);

    const handleError = (error: any) => {
        setState((prev) => ({
            ...prev,
            error: error as Error,
            header: 'Error',
            message: error.toString(),
            loading: false,
        }));
        pollingRef.current = false;
    };

    useEffect(() => {
        const fetchData = async () => {
            await Promise.all([fetchMeta(), fetchEpisodes()]);
        };

        fetchData();

        const intervalId = setInterval(() => {
            if (pollingRef.current) {
                if (pollingAttemptsRef.current >= MAX_POLLING_ATTEMPTS) {
                    handleError(new Error('Max polling attempts reached'));
                    cancelSourceRef.current!.cancel('Polling stopped');
                    pollingRef.current = false;
                    clearInterval(intervalId);
                } else {
                    fetchData();
                }
            } else {
                clearInterval(intervalId);
            }
        }, POLLING_INTERVAL);
    }, [fetchEpisodes, fetchMeta, episodesPolling, metaPolling, id, args]);

    useEffect(() => {
        if (!index) index = '1';

        if (state.episodes && state.meta) {
            if (index! > state.meta.length) {
                handleError('Invalid episode');
            }
            if (state.episodes.length === state.meta.length) {
                pollingRef.current = false;
            }

            state.currentEpisode = state.episodes.find(
                (ep) => ep.episode_index === index,
            );
            if (state.currentEpisode) {
                setState((prev) => ({
                    ...prev,
                    message: 'Done',
                    loading: false,
                }));
            }
        }
    }, [state.episodes, state.meta, index]);

    return state;
}
