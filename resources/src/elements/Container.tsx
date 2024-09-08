import React from 'react';
import tw from 'twin.macro';
import styled from '@emotion/styled';

interface ContainerProps {
    children: React.ReactNode;
}

const StyledContainer = styled.div`
    ${tw`flex w-full max-w-[2000px] mx-auto`}
`;

const Container: React.FC<ContainerProps> = ({ children }) => (
    <StyledContainer>{children}</StyledContainer>
);

export default Container;
