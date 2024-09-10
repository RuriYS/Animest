import { Footer, Header, Main } from './components';

import { ContentContainer, MainContainer } from './elements';
import { BrowserRouter } from 'react-router-dom';
import React from 'react';
import ReactDOM from 'react-dom/client';

const App = () => {
    return (
        <BrowserRouter>
            <MainContainer>
                <Header />
                <Main />
                <Footer />
            </MainContainer>
        </BrowserRouter>
    );
};

const root = ReactDOM.createRoot(document.getElementById('root')!);
root.render(<App />);
