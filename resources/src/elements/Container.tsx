import React from 'react';

interface ContainerProps {
    children: React.ReactNode;
}

const Container: React.FC<ContainerProps> = ({ children }) => (
    <div className='flex w-full max-w-[2000px] mx-auto'>{children}</div>
);

export default Container;
