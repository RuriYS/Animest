import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { Anime, Home, News, Queues, Search, Terms, Watch } from './components';

const Main = () => {
    return (
        <Routes>
            <Route path='*' element={<Navigate to={'/home'} />} />
            <Route path='/home' element={<Home />} />
            <Route path='/catalog' />
            <Route path='/news' element={<News />} />
            <Route path='/queues' element={<Queues />} />
            <Route path='/anime/:id' element={<Anime />} />
            <Route path='/terms' element={<Terms />} />
            <Route path='/search' element={<Search />} />
            <Route path='/watch/:id/episode/:episodeId' element={<Watch />} />
        </Routes>
    );
};

export default Main;
