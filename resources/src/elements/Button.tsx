import tw from 'twin.macro';
import styled from '@emotion/styled';

const Button = styled.button(
    ({ variant }: { variant: 'primary' | 'secondary' }) => [
        tw`h-full min-w-20 px-4 py-2 truncate text-sm font-medium rounded-lg flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed`,
        variant == 'primary' && tw`bg-neutral-300 text-black`,
        variant == 'secondary' && tw`bg-neutral-800 text-white`,
    ],
);

export default Button;
