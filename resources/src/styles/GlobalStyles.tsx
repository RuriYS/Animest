import React from 'react';
import { css, Global } from '@emotion/react';
import tw, { theme, GlobalStyles as BaseStyles } from 'twin.macro';

const customStyles = css({
    body: {
        WebkitTapHighlightColor: theme`colors.transparent`,
        ...tw`antialiased`,
    },
});

const GlobalStyles = () => (
    <>
        <BaseStyles />
        <Global styles={customStyles} />
    </>
);

export default GlobalStyles;
