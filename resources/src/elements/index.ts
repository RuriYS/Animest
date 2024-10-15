import { lazy } from 'react';

const Constraint = lazy(() => import('../components/Constraint'));
const ContentContainer = lazy(() => import('../components/ContentContainer'));
const Episode = lazy(() => import('./Episode'));
const EpisodeCard = lazy(() => import('./EpisodeCard'));
const EpisodeInfo = lazy(() => import('./EpisodeInfo'));
const EpisodeList = lazy(() => import('./EpisodeList'));
const MainContainer = lazy(() => import('./MainContainer'));
const Moment = lazy(() => import('./Moment'));
const Pagination = lazy(() => import('./Pagination'));
const PlayerWrapper = lazy(() => import('./PlayerWrapper'));
const SortSelect = lazy(() => import('./SortSelect'));
const SearchResult = lazy(() => import('./SearchResult'));
const WatchInfoContainer = lazy(() => import('./WatchInfoContainer'));

export {
    Constraint,
    ContentContainer,
    Episode,
    EpisodeCard,
    EpisodeInfo,
    EpisodeList,
    MainContainer,
    Moment,
    Pagination,
    PlayerWrapper,
    SortSelect,
    SearchResult,
    WatchInfoContainer,
};
