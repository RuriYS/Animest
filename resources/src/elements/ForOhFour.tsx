import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import ContentContainer from './ContentContainer';
import Constraint from './Constraint';
import React from 'react';

const RedirectToRickroll = () => {
    const navigate = useNavigate();

    useEffect(() => {
        window.location.href = '/videos/idiot.mp4';
    }, [navigate]);

    return null;
};

export default function Component() {
    return (
        <Constraint>
            <ContentContainer>
                <h1 className='text-xl'>404 not found</h1>
                <RedirectToRickroll />
            </ContentContainer>
        </Constraint>
    );
}
