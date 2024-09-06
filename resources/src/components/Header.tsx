import React from 'react';
import { Link } from 'react-router-dom';

const Header = () => {
    return (
        <div className='fixed w-full z-20 bg-gradient-to-b from-gray-900 to-transparent'>
            <header className='flex justify-between items-center outline-gray-400 p-4 text-white max-w-[2000px] mx-auto'>
                <div className='flex items-center'>
                    <h1 className='text-3xl font-bold'>Animei</h1>
                    <nav className='ml-10 space-x-4'>
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
                    </nav>
                </div>
                <div className='flex items-center space-x-4'>
                    <input
                        type='text'
                        placeholder='Search'
                        className='relative px-4 py-2 bg-gray-800 focus:bg-slate-300 focus:text-black text-white rounded outline-none'
                    />
                    <button className='bg-gray-800 px-4 py-2 rounded hover:bg-blue-700'>
                        Log In
                    </button>
                    <button className='bg-white text-gray-900 px-4 py-2 rounded hover:bg-gray-200'>
                        Get Started
                    </button>
                </div>
            </header>
        </div>
    );
};

export default Header;
