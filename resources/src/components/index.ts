import { lazy } from 'react';

const Title = lazy(() => import('./Title'));
const Footer = lazy(() => import('./Footer'));
const Header = lazy(() => import('./Header'));
const Home = lazy(() => import('./Home'));
const LoadingFallback = lazy(() => import('./LoadingFallback'));
const News = lazy(() => import('./News'));
const Queues = lazy(() => import('./Queues'));
const Search = lazy(() => import('./Browse'));
const Terms = lazy(() => import('./Terms'));
const Section = lazy(() => import('./Section'));
const Watch = lazy(() => import('./Watch'));
const FourOFour = lazy(() => import('./404'));

export {
    Title,
    Footer,
    FourOFour,
    Header,
    Home,
    LoadingFallback,
    News,
    Queues,
    Search,
    Terms,
    Section,
    Watch,
};
