import React from 'react';
import { FaTelegram, FaDiscord, FaYoutube, FaInstagram } from 'react-icons/fa';
import Container from '../elements/Container';
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

const FooterLink = styled(Link)<FooterLinkProps>`
    ${tw`hover:text-white`}
    ${({ bold }) => bold && tw`font-bold`}
    ${({ small }) => small && tw`text-xs`}
`;

const Footer = () => {
    return (
        <FooterContainer>
            <Container>
                <LinksContainer>
                    <FooterLink to='/' bold>
                        Animei.moe
                    </FooterLink>
                    <FooterLink to='/terms' small>
                        Terms & Privacy
                    </FooterLink>
                    <FooterLink to='/contacts' small>
                        Contacts
                    </FooterLink>
                </LinksContainer>
                <SocialLinks>
                    <FooterLink to='#'>
                        <FaTelegram size={20} />
                    </FooterLink>
                    <FooterLink to='#'>
                        <FaDiscord size={20} />
                    </FooterLink>
                    <FooterLink to='#'>
                        <FaYoutube size={20} />
                    </FooterLink>
                    <FooterLink to='#'>
                        <FaInstagram size={20} />
                    </FooterLink>
                </SocialLinks>
            </Container>
        </FooterContainer>
    );
};

export default Footer;
