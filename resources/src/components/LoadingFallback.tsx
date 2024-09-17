import { Loader2 } from 'lucide-react';
import React from 'react';

export default function Component() {
    return (
        <div className='flex flex-col items-center justify-center min-h-screen bg-background'>
            <Loader2 className='h-16 w-16 animate-spin text-primary' />
            <h2 className='mt-4 text-2xl font-semibold text-foreground'>
                Loading...
            </h2>
            <p className='mt-2 text-muted-foreground'>
                Please wait while we fetch your content.
            </p>
        </div>
    );
}
