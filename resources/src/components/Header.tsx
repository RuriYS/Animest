import React from 'react';
import { Link } from 'react-router-dom';
import tw from 'twin.macro';
import styled from '@emotion/styled';

const HeaderContainer = styled.div`
    ${tw`fixed w-full z-20 bg-gradient-to-b from-gray-900 to-transparent`}
`;

const HeaderContent = styled.header`
    ${tw`flex justify-between items-center outline-gray-400 p-4 text-white max-w-[2000px] mx-auto`}
`;

const NavContainer = styled.nav`
    ${tw`ml-10 space-x-4`}
`;

const SearchInput = styled.input`
    ${tw`relative px-4 py-2 bg-gray-800 focus:bg-slate-300 focus:text-black text-white rounded outline-none`}
`;

const Button = styled.button(
    ({ variant }: { variant: 'primary' | 'secondary' }) => [
        variant === 'primary'
            ? tw`bg-gray-800 px-4 py-2 rounded hover:bg-blue-700`
            : tw`bg-white text-gray-900 px-4 py-2 rounded hover:bg-gray-200`,
    ],
);

const Header = () => {
    return (
        <HeaderContainer>
            <HeaderContent>
                <div className='flex items-center'>
                    <Link to='/'>
                        <h1 className='text-3xl font-bold'>Animest</h1>
                    </Link>
                    <NavContainer>
                        <Link to='/' className='hover:text-gray-300'>
                            Home
                        </Link>
                        <Link to='/catalog' className='hover:text-gray-300'>
                            Catalog
                        </Link>
                        <Link to='/news' className='hover:text-gray-300'>
                            News
                        </Link>
                        <Link to='/collections' className='hover:text-gray-300'>
                            Collections
                        </Link>
                    </NavContainer>
                </div>
                <div className='flex items-center space-x-4'>
                    <SearchInput type='text' placeholder='Search' />
                    <Button variant='primary'>Log In</Button>
                    <Button variant='secondary'>Get Started</Button>
                </div>
            </HeaderContent>
        </HeaderContainer>
    );
};

export default Header;
