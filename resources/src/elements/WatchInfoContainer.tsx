import { Loader2, AlertCircle } from 'lucide-react';
import React from 'react';
import Constraint from '../components/Constraint';
import ContentContainer from '../components/ContentContainer';

export default function WatchInfoContainer({
    state,
    header,
    message,
}: {
    state: 'loading' | 'error';
    header?: string;
    message: string;
}) {
    return (
        <Constraint>
            <ContentContainer>
                <div className='flex flex-col gap-4 items-center'>
                    {state === 'loading' ? (
                        <Loader2 className='w-16 h-16 animate-spin' />
                    ) : (
                        <AlertCircle className='w-16 h-16 text-red-500' />
                    )}
                    <p className='text-base'>{header}</p>
                    <p className='muted text-xs'>{message}</p>
                </div>
            </ContentContainer>
        </Constraint>
    );
}
