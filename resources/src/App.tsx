import { BrowserRouter } from 'react-router-dom';
import React, { Suspense, lazy } from 'react';
import ReactDOM from 'react-dom/client';
import Main from '@/Main';
import GlobalStyles from './styles/GlobalStyles';
import MainContainer from './elements/MainContainer';
import { Header, Footer, LoadingFallback } from '@/components';

const App = () => {
    return (
        <BrowserRouter>
            <Suspense fallback={<LoadingFallback />}>
                <MainContainer>
                    <Header />
                    <Main />
                    <Footer />
                </MainContainer>
            </Suspense>
        </BrowserRouter>
    );
};

const root = ReactDOM.createRoot(document.getElementById('root')!);
root.render(
    <React.StrictMode>
        <GlobalStyles />
        <App />
    </React.StrictMode>,
);
