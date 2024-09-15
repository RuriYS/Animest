import { Link } from 'react-router-dom';
import tw, { styled } from 'twin.macro';

export default styled(Link)`
    ${tw`relative flex gap-x-4 bg-neutral-800 hover:bg-neutral-500 rounded-lg transition-colors duration-300 ease-in-out`}

    img {
        ${tw`rounded-lg`}
    }

    h1,
    p {
        ${tw`shadow-lg`}
    }

    p {
        ${tw`text-neutral-300`}
        font-size: 0.85rem;
    }

    span {
        font-size: 0.7rem;
    }
`;
