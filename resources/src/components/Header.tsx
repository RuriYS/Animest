import React, { useRef, useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import tw from 'twin.macro';
import styled from '@emotion/styled';
import MorphableSearchBar from '../elements/MorphableSearchBar';
import { FaBars } from 'react-icons/fa';
import { CSSTransition, TransitionGroup } from 'react-transition-group';

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
    ${tw`flex justify-between gap-x-4`}
`;

const RightNav = styled.div`
    ${tw`hidden md:flex`}
`;

const MobileNav = styled.div`
    ${tw`flex flex-col md:hidden p-4 rounded-lg h-full`}
    background-color: rgba(0, 0, 0, 80%)
`;

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
                        <Navlinks className='hidden md:flex'>
                            <Link to='/home'>Home</Link>
                            <Link to='/catalog'>Catalog</Link>
                            <Link to='/news'>News</Link>
                            <Link to='/collections'>Collections</Link>
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
                            <Navlinks>
                                <Link to='/home'>Home</Link>
                                <Link to='/catalog'>Catalog</Link>
                                <Link to='/news'>News</Link>
                                <Link to='/collections'>Collections</Link>
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
