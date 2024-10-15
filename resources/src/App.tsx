import React, { Suspense, lazy } from 'react';
import { BrowserRouter } from 'react-router-dom';
import { Header, Footer, LoadingFallback } from '@/components';
import GlobalStyles from '@/styles/GlobalStyles';
import MainContainer from '@/elements/MainContainer';
import ReactDOM from 'react-dom/client';
import Routes from '@/Routes';

const App = () => {
    return (
        <BrowserRouter>
            <Suspense fallback={<LoadingFallback />}>
                <MainContainer>
                    <Header />
                    <Routes />
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
