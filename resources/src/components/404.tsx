import React from 'react';
import { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useNavigate } from 'react-router-dom';
import Constraint from '@/components/Constraint';
import ContentContainer from '@/components/ContentContainer';

const Rickroll = () => {
    const navigate = useNavigate();

    useEffect(() => {
        if (window.location.href.includes('admin'))
            window.location.href = '/videos/idiot.mp4';
    }, [navigate]);

    return null;
};

export default function () {
    return (
        <Constraint>
            <ContentContainer>
                <div className='flex flex-col gap-4 mx-auto text-center'>
                    <h1 className='text-2xl'>404 | Are you lost?</h1>
                    <Link to={'/home'}>
                        <p className='text-lg underline'>Take me home.</p>
                    </Link>
                </div>
                <Rickroll />
            </ContentContainer>
        </Constraint>
    );
}
