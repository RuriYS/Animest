import styled from '@emotion/styled';
import tw from 'twin.macro';

const MainContainer = styled.main`
    ${tw`relative overflow-x-hidden flex flex-col`}

    .fade-enter {
        opacity: 0;
    }

    .fade-enter-active {
        opacity: 1;
        transition: opacity 500ms ease-in-out;
    }

    .fade-exit {
        opacity: 1;
    }

    .fade-exit-active {
        opacity: 0;
        transition: opacity 500ms ease-in-out;
    }
`;

export default MainContainer;
