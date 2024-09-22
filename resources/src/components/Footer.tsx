import React from 'react';
import { FaTelegram, FaDiscord, FaYoutube, FaInstagram } from 'react-icons/fa';
import Constraint from '../elements/Constraint';
import styled from '@emotion/styled';
import { Link } from 'react-router-dom';
import tw from 'twin.macro';

interface FooterLinkProps {
    bold?: boolean;
    small?: boolean;
}

const FooterContainer = styled.footer`
    ${tw`w-full bg-black text-gray-400 p-4 flex justify-between`}
`;

const LinksContainer = styled.div`
    ${tw`flex flex-grow space-x-4 items-center text-center`}
`;

const SocialLinks = styled.div`
    ${tw`flex space-x-4`}
`;

const FooterLink = styled(Link)(
    ({ variant }: { variant?: 'bold' | 'small' }) => [
        tw`hover:text-white truncate`,
        variant == 'bold' && tw`font-bold`,
        variant == 'small' && tw`text-xs`,
    ],
);

const Footer = () => {
    return (
        <FooterContainer>
            <Constraint>
                <LinksContainer>
                    <FooterLink to='/' variant='bold'>
                        Animest.land
                    </FooterLink>
                    <FooterLink to='/terms' variant='small'>
                        Terms & Conditions
                    </FooterLink>
                </LinksContainer>
                <SocialLinks>
                    <FooterLink to='https://discord.gg/ZFXA7Wf2Ra'>
                        <FaDiscord size={20} />
                    </FooterLink>
                </SocialLinks>
            </Constraint>
        </FooterContainer>
    );
};

export default Footer;
