import React, { useState, useEffect } from 'react';
import { Constraint, ContentContainer } from '.';

export default function WatchLoading({ message }: { message: string }) {
    const [showMessage, setShowMessage] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            setShowMessage(true);
        }, 1000);

        return () => clearTimeout(timer);
    }, []);

    return (
        <Constraint>
            <ContentContainer>
                <div className='flex flex-col gap-4'>
                    <h1 className='text-lg'>The episode is loading...</h1>
                    {showMessage && (
                        <>
                            <p className='muted'>{message}</p>
                        </>
                    )}
                </div>
            </ContentContainer>
        </Constraint>
    );
}
