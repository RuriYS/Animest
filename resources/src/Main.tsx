import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { Anime, Home, News, Collections, Terms, Watch } from './components';

const Main = () => {
    return (
        <Routes>
            <Route path='*' element={<Navigate to={'/home'} />} />
            <Route path='/home' element={<Home />} />
            <Route path='/catalog' />
            <Route path='/news' element={<News />} />
            <Route path='/collections' element={<Collections />} />
            <Route path='/anime/:id' element={<Anime />} />
            <Route path='/terms' element={<Terms />} />
            <Route path='/watch/:id/episode/:episodeId' element={<Watch />} />
        </Routes>
    );
};

export default Main;
