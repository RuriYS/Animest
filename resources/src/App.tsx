import React, { Suspense, lazy } from 'react';
import { BrowserRouter } from 'react-router-dom';
import ReactDOM from 'react-dom/client';

const MainContainer = lazy(() => import('./elements/MainContainer'));
const GlobalStyles = lazy(() => import('./styles/GlobalStyles'));
const Footer = lazy(() => import('./components/Footer'));
const Header = lazy(() => import('./components/Header'));
const Main = lazy(() => import('./Main'));

const App = () => {
    return (
        <BrowserRouter>
            <Suspense fallback={<div></div>}>
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
