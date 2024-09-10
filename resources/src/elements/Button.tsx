import tw from 'twin.macro';
import styled from '@emotion/styled';

const Button = styled.button(
    ({ variant }: { variant: 'primary' | 'secondary' }) => [
        tw`h-full px-4 py-2 truncate text-xs font-medium rounded-lg flex items-center justify-center space-x-2`,
        variant == 'primary' && tw`bg-gray-800 text-white`,
        variant == 'secondary' && tw`bg-white text-black`,
    ],
);

export default Button;
