import tw from 'twin.macro';
import styled from '@emotion/styled';
import { css } from '@emotion/react';

const FadeContainer = styled.div`
    ${tw`flex flex-col`}
    ${tw`relative`};

    & > .fade-enter {
        opacity: 0;
    }

    & > .fade-enter-active {
        opacity: 1;
    }

    & > .fade-exit {
        opacity: 1;
    }

    & > .fade-exit-active {
        opacity: 0;
    }
`;

export default FadeContainer;
