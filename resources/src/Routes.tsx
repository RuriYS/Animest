import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { Home, News, Queues, Search, Terms, Watch } from '@/components';
import { FourOFour } from '@/components';

export default function () {
    return (
        <Routes>
            <Route path='/' element={<Navigate to={'/home'} />} />
            <Route path='/home' element={<Home />} />
            <Route path='/browse' element={<Search />} />
            <Route path='/catalog' />
            <Route path='/news' element={<News />} />
            <Route path='/queues' element={<Queues />} />
            <Route path='/terms' element={<Terms />} />
            <Route path='/watch/:id' element={<Watch />} />
            <Route path='/watch/:id/episode/:index' element={<Watch />} />
            <Route path='*' element={<FourOFour />} />
        </Routes>
    );
}
