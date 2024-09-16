import React from 'react';
import { Link } from 'react-router-dom';

interface Props {
    id: string;
    title: string;
    image: string;
    year: string;
}

export default function SearchResult({ id, title, image, year }: Props) {
    return (
        <Link
            to={`/watch/${id}/episode/1`}
            className='relative flex bg-neutral-600 overflow-hidden rounded-lg aspect-[2/3]'
        >
            <img
                src={image}
                alt={id}
                className='absolute inset-0 w-full h-full object-cover opacity-50 hover:opacity-80 transition-opacity'
            />
            <div className='absolute inset-0 bg-gradient-to-t from-black to-transparent'></div>
            <div className='absolute bottom-0 left-0 right-0 p-4'>
                <h3 className='font-bold text-white mb-1 line-clamp-2'>
                    {title}
                </h3>
                <p className='text-xs text-gray-300'>{year}</p>
            </div>
        </Link>
    );
}
