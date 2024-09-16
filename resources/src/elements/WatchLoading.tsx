import React, { useState, useEffect } from 'react';
import { Constraint, ContentContainer } from '.';

export default function WatchLoading() {
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
                    <h1>The episode is loading...</h1>
                    {showMessage && (
                        <>
                            <p>
                                Our tiny spiders are doing their best to crawl
                                the data for you, please wait {'<3'}
                            </p>
                            <p>We'll let you know when it's ready.</p>
                        </>
                    )}
                </div>
            </ContentContainer>
        </Constraint>
    );
}
