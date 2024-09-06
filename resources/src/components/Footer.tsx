import React from 'react';
import { FaTelegram, FaDiscord, FaYoutube, FaInstagram } from 'react-icons/fa';
import Container from '../elements/Container';

const Footer = () => {
    return (
        <footer className='w-full bg-black text-gray-400 p-4 flex justify-between'>
            <Container>
                <div className='flex flex-grow space-x-4 items-center text-center'>
                    <a href='#' className='hover:text-white font-bold'>
                        Animei.moe
                    </a>
                    <a href='#' className='hover:text-white text-xs'>
                        Terms & Privacy
                    </a>
                    <a href='#' className='hover:text-white text-xs'>
                        Contacts
                    </a>
                </div>
                <div className='flex space-x-4'>
                    <a href='#' className='hover:text-white'>
                        <FaTelegram size={20} />
                    </a>
                    <a href='#' className='hover:text-white'>
                        <FaDiscord size={20} />
                    </a>
                    <a href='#' className='hover:text-white'>
                        <FaYoutube size={20} />
                    </a>
                    <a href='#' className='hover:text-white'>
                        <FaInstagram size={20} />
                    </a>
                </div>
            </Container>
        </footer>
    );
};

export default Footer;
