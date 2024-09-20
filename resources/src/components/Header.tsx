import React, { useRef, useState } from 'react';
import { Link, To, useLocation, useNavigate } from 'react-router-dom';
import tw from 'twin.macro';
import styled from '@emotion/styled';
import MorphableSearchBar from '../elements/MorphableSearchBar';
import { FaBars } from 'react-icons/fa';
import { CSSTransition } from 'react-transition-group';

const HeaderContainer = styled.div`
    ${tw`fixed h-12 w-full z-20 bg-gradient-to-b from-black to-transparent`}
`;

const HeaderContent = styled.header`
    ${tw`flex justify-between items-center text-white max-w-[2000px] mx-auto`}
`;

const NavContainer = styled.nav`
    ${tw`text-sm flex flex-col md:flex-row w-full p-4 gap-y-4 justify-between md:items-center`}
`;

const Navlinks = styled.div`
    ${tw`flex flex-col md:flex-row gap-x-6 gap-y-4 items-center rounded-lg transition-all duration-300 ease-in-out`}
`;

const LeftNav = styled.div`
    ${tw`flex justify-between gap-x-8`}
`;

const RightNav = styled.div`
    ${tw`hidden md:flex`}
`;

const MobileNav = styled.div`
    ${tw`flex flex-col space-y-4 absolute inset-0 h-[250px] -z-10 p-4 pt-[60px] md:hidden rounded-lg`}
    background-color: rgba(0, 0, 0, 80%)
`;

const LINK = styled(Link)`
    ${tw`text-gray-200 text-[0.8rem] hover:text-white transition-all duration-300 ease-in-out`}
`;

const links = [
    {
        path: '/home',
        label: 'Home',
    },
    {
        path: '/browse',
        label: 'Browse',
    },
];

const Header = () => {
    const [expanded, setExpanded] = useState(false);
    const nodeRef = useRef(null);

    return (
        <HeaderContainer>
            <HeaderContent>
                <NavContainer>
                    <LeftNav>
                        <Link to='/'>
                            <h1 className='text-2xl md:text-3xl font-bold'>
                                Animest
                            </h1>
                        </Link>
                        <Navlinks className='hidden md:flex text-gray-500 hover:text-white'>
                            {links.map(({ path, label }) => {
                                return (
                                    <LINK key={path} to={path as To}>
                                        {label}
                                    </LINK>
                                );
                            })}
                        </Navlinks>
                        <FaBars
                            className='md:hidden'
                            size={24}
                            onClick={() => setExpanded(!expanded)}
                        />
                    </LeftNav>
                    <CSSTransition
                        in={expanded}
                        timeout={300}
                        classNames='mobile-nav'
                        unmountOnExit
                        nodeRef={nodeRef}
                    >
                        <MobileNav ref={nodeRef}>
                            <Navlinks className='text-gray-500 hover:text-white'>
                                {links.map(({ path, label }) => {
                                    return (
                                        <LINK key={path} to={path as To}>
                                            {label}
                                        </LINK>
                                    );
                                })}
                            </Navlinks>
                            <MorphableSearchBar />
                        </MobileNav>
                    </CSSTransition>

                    <RightNav>
                        <MorphableSearchBar />
                    </RightNav>
                </NavContainer>
            </HeaderContent>
        </HeaderContainer>
    );
};

export default Header;
