import { Anime, Collections, Home, News, Terms } from './components';
import {
    BrowserRouter as Router,
    Routes,
    Route,
    useLocation,
} from 'react-router-dom';
import Footer from './components/Footer';
import Header from './components/Header';
import ReactDOM from 'react-dom/client';
import React, {
    createContext,
    useContext,
    useState,
    useEffect,
    ReactNode,
} from 'react';

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

const Main = () => {
    const { headerVisible } = useHeader();

    return (
        <main className='flex flex-col'>
            {headerVisible && <Header />}
            <div className='flex-grow min-h-full'>
                <Routes>
                    <Route path='/' element={<Home />} />
                    <Route path='/anime' element={<Anime />} />
                    <Route path='/collections' element={<Collections />} />
                    <Route path='/news' element={<News />} />
                    <Route path='/terms' element={<Terms />} />
                </Routes>
            </div>
            <Footer />
        </main>
    );
};

const main = ReactDOM.createRoot(document.getElementById('root')!);
main.render(<App />);
