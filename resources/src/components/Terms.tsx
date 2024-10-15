import styled from '@emotion/styled';
import React from 'react';
import tw from 'twin.macro';

interface LinkProps {
    href: string;
}

const TermsContainer = styled.div`
    ${tw`text-white p-8 mt-12 max-w-4xl mx-auto`}
`;

const Title = styled.h1`
    ${tw`text-4xl font-bold mb-8 text-center`}
`;

const Section = styled.div`
    ${tw`mb-6`}
`;

const SectionTitle = styled.h2`
    ${tw`text-2xl font-semibold mb-4`}
`;

const Paragraph = styled.p`
    ${tw`mb-4 leading-relaxed text-sm text-neutral-300`}
`;

const Link = styled.a<LinkProps>`
    ${tw`text-blue-300`}
`;

const Terms = () => {
    return (
        <TermsContainer>
            <Title>Terms And Conditions</Title>

            <Section>
                <SectionTitle>Introduction</SectionTitle>
                <Paragraph>
                    Welcome to Animest ("we", "our", or "us"). These Terms and
                    Conditions ("Terms") govern your use of our website and
                    services. By accessing or using Animest, you agree to comply
                    with and be bound by these Terms.
                </Paragraph>
            </Section>

            <Section>
                <SectionTitle>Disclaimer</SectionTitle>
                <Paragraph>
                    Animest is a free anime streaming platform. We do not host,
                    own or control the content available through our website.
                    All anime content provided through our site is hosted on
                    third-party servers and is the property of the respective
                    copyright holders. We do not claim any rights to this
                    content.
                </Paragraph>
            </Section>

            <Section>
                <SectionTitle>Copyright Notice</SectionTitle>
                <Paragraph>
                    All content, trademarks, and other intellectual property
                    rights related to the anime and media featured on Animest
                    belong to their respective owners. Animest makes no claims
                    of ownership over any copyrighted material.
                </Paragraph>
            </Section>

            <Section>
                <SectionTitle>
                    No Responsibility for Third-Party Content
                </SectionTitle>
                <Paragraph>
                    We are not responsible for the availability, legality, or
                    accuracy of the third-party content linked or embedded on
                    Animest. The inclusion of any links or references to
                    third-party content does not imply endorsement or support of
                    that content by Animest.
                </Paragraph>
            </Section>

            <Section>
                <SectionTitle>Limitation of Liability</SectionTitle>
                <Paragraph>
                    Animest provides its services on an "as is" and "as
                    available" basis. We disclaim all warranties and
                    representations, whether express or implied, to the extent
                    permitted by law. We will not be liable for any direct,
                    indirect, incidental, consequential, or punitive damages
                    arising from your use of Animest.
                </Paragraph>
            </Section>

            <Section>
                <SectionTitle>User Conduct</SectionTitle>
                <Paragraph>
                    You agree to use Animest in compliance with all applicable
                    laws and regulations. You must not use the website for any
                    illegal or unauthorized purpose. Any misuse of the website
                    or violation of these Terms may result in termination of
                    your access.
                </Paragraph>
            </Section>

            <Section>
                <SectionTitle>Changes to Terms</SectionTitle>
                <Paragraph>
                    We reserve the right to modify these Terms at any time. Any
                    changes will be posted on this page with an updated revision
                    date. Your continued use of Animest after any changes
                    indicates your acceptance of the new Terms.
                </Paragraph>
            </Section>

            <Section>
                <SectionTitle>Contact Us</SectionTitle>
                <Paragraph>
                    If you have any questions about these Terms, please contact
                    us at
                    <Link href='mailto:mail@animest.xyz'>
                        {' '}
                        mail@animest.xyz
                    </Link>
                </Paragraph>
            </Section>

            <Paragraph className='text-xs text-neutral-500'>
                Last Updated: 5th of September 06:30 UTC
            </Paragraph>
        </TermsContainer>
    );
};

export default Terms;
