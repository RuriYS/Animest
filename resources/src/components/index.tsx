import { lazy } from 'react';

import Header from './Header';
import Home from './Home';
import Footer from './Footer';
import LoadingFallback from './LoadingFallback';

export const Anime = lazy(() => import('./Anime'));
export const Queues = lazy(() => import('./Queues'));
export const News = lazy(() => import('./News'));
export const Terms = lazy(() => import('./Terms'));
export const Search = lazy(() => import('./Search'));
export const TitleSection = lazy(() => import('./TitleSection'));
export const Watch = lazy(() => import('./Watch'));

export { Header, Home, Footer, LoadingFallback };
