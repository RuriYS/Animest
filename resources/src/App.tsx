import {
    Anime,
    Collections,
    Home,
    News,
    Terms,
    Footer,
    Header,
} from './components';
import {
    BrowserRouter as Router,
    Routes,
    Route,
    useLocation,
} from 'react-router-dom';
import ReactDOM from 'react-dom/client';
import React, {
    createContext,
    useContext,
    useState,
    useEffect,
    ReactNode,
} from 'react';
import styled from '@emotion/styled';
import tw from 'twin.macro';
import { CSSTransition, TransitionGroup } from 'react-transition-group';
interface HeaderContextType {
    headerVisible: boolean;
    setHeaderVisible: React.Dispatch<React.SetStateAction<boolean>>;
}

const HeaderContext = createContext<HeaderContextType | undefined>(undefined);

const useHeader = () => {
    const context = useContext(HeaderContext);
    if (!context) {
        throw new Error('useHeader must be used within a HeaderProvider');
    }
    return context;
};

const HeaderProvider = ({ children }: { children: ReactNode }) => {
    const [headerVisible, setHeaderVisible] = useState(true);
    const location = useLocation();

    useEffect(() => {
        if (location.pathname === '/terms') {
            setHeaderVisible(false);
        } else {
            setHeaderVisible(true);
        }
    }, [location.pathname]);

    return (
        <HeaderContext.Provider value={{ headerVisible, setHeaderVisible }}>
            {children}
        </HeaderContext.Provider>
    );
};

const App = () => {
    return (
        <Router>
            <HeaderProvider>
                <Main />
            </HeaderProvider>
        </Router>
    );
};

const MainContainer = styled.main`
    ${tw`flex flex-col`}

    .fade-enter {
        opacity: 0;
    }

    .fade-enter-active {
        opacity: 1;
        transition: opacity 500ms ease-in-out;
    }

    .fade-exit {
        opacity: 1;
    }

    .fade-exit-active {
        opacity: 0;
        transition: opacity 500ms ease-in-out;
    }
`;

const Main = () => {
    const location = useLocation();
    const { headerVisible } = useHeader();

    return (
        <MainContainer>
            {headerVisible && <Header />}
            <TransitionGroup className={'flex-grow min-h-full'}>
                <CSSTransition
                    key={location.pathname}
                    classNames='fade'
                    timeout={300}
                >
                    <Routes location={location}>
                        <Route path='/' element={<Home />} />
                        <Route path='/anime' element={<Anime />} />
                        <Route path='/collections' element={<Collections />} />
                        <Route path='/news' element={<News />} />
                        <Route path='/terms' element={<Terms />} />
                    </Routes>
                </CSSTransition>
            </TransitionGroup>
            <Footer />
        </MainContainer>
    );
};

const main = ReactDOM.createRoot(document.getElementById('root')!);
main.render(<App />);
