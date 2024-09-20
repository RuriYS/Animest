import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { Anime, Home, News, Queues, Search, Terms, Watch } from '@/components';

const Main = () => {
    return (
        <Routes>
            <Route path='*' element={<Navigate to={'/home'} />} />
            <Route path='/home' element={<Home />} />
            <Route path='/browse' element={<Search />} />
            <Route path='/catalog' />
            <Route path='/news' element={<News />} />
            <Route path='/queues' element={<Queues />} />
            <Route path='/terms' element={<Terms />} />
            <Route path='/watch/:id' element={<Watch />} />
            <Route
                path='/watch/:id/episode/:episodeIndex'
                element={<Watch />}
            />
        </Routes>
    );
};

export default Main;
