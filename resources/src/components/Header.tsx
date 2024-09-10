import React from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import tw from 'twin.macro';
import styled from '@emotion/styled';
import Button from '../elements/Button';
import MorphableSearchBar from '../elements/MorphableSearchBar';

const HeaderContainer = styled.div`
    ${tw`fixed h-12 w-full z-20 bg-gradient-to-b from-gray-900 to-transparent`}
`;

const HeaderContent = styled.header`
    ${tw`flex justify-between items-center outline-gray-400 p-4 text-white max-w-[2000px] mx-auto`}
`;

const NavContainer = styled.nav`
    ${tw`text-sm ml-10 space-x-4 hidden md:flex`}
`;

const MobileNav = styled.select`
    ${tw`md:hidden w-24 truncate block bg-transparent text-sm text-white px-4 py-2 rounded-lg focus:outline-none appearance-none`}

    background-image: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white"%3E%3Cpath fill-rule="evenodd" d="M5.293 7.707a1 1 0 011.414 0L10 11.586l3.293-3.879a1 1 0 111.414 1.414l-4 4.5a1 1 0 01-1.414 0l-4-4.5a1 1 0 010-1.414z" clip-rule="evenodd" /%3E%3C/svg%3E');
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem 1rem;

    transition: border-color 0.3s ease;
`;

const LeftNav = styled.div`
    ${tw`flex items-center`}
`;

const RightNav = styled.div`
    ${tw`flex items-center space-x-4`}
`;

const Header = () => {
    const location = useLocation();
    const navigate = useNavigate();

    const handleNavigation = (event: React.ChangeEvent<HTMLSelectElement>) => {
        navigate(event.target.value);
    };

    const getLinkClassName = (path: string) =>
        location.pathname === path ? 'text-white' : 'text-slate-300';

    return (
        <HeaderContainer>
            <HeaderContent>
                <LeftNav>
                    <Link to='/'>
                        <h1 className='text-2xl md:text-3xl font-bold'>
                            Animest
                        </h1>
                    </Link>
                    <NavContainer>
                        <Link to='/home' className={getLinkClassName('/')}>
                            Home
                        </Link>
                        <Link
                            to='/catalog'
                            className={getLinkClassName('/catalog')}
                        >
                            Catalog
                        </Link>
                        <Link to='/news' className={getLinkClassName('/news')}>
                            News
                        </Link>
                        <Link
                            to='/collections'
                            className={getLinkClassName('/collections')}
                        >
                            Collections
                        </Link>
                    </NavContainer>

                    <MobileNav
                        value={location.pathname}
                        onChange={handleNavigation}
                    >
                        <option value='/home'>Home</option>
                        <option value='/catalog'>Catalog</option>
                        <option value='/news'>News</option>
                        <option value='/collections'>Collections</option>
                    </MobileNav>
                </LeftNav>
                <RightNav>
                    <MorphableSearchBar />
                    <Button variant='primary'>Log In</Button>
                    <Button variant='secondary'>Get Started</Button>
                </RightNav>
            </HeaderContent>
        </HeaderContainer>
    );
};

export default Header;
