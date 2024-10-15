import { Title } from '@/components/Section';
import { Button } from '@/components/ui/button';
import { Play } from 'lucide-react';
import React from 'react';
import { Link, useNavigate } from 'react-router-dom';

const SectionCard = ({
    item,
    genres,
    episode_index,
}: {
    item: Title;
    genres: string[];
    episode_index: string;
}) => {
    const navigate = useNavigate();

    return (
        <div
            key={item.latest_episode_id}
            className='relative flex-none w-[28%] md:w-64 max-w-80 h-64 md:h-96 cursor-pointer group/item transition-all duration-300 select-none'
            onClick={() =>
                navigate(`/watch/${item.id}/episode/${episode_index}`)
            }
        >
            <div className='relative w-full h-full overflow-hidden rounded-md'>
                <img
                    src={item.thumbnail}
                    alt={item.title}
                    className='absolute top-0 left-0 w-full h-full object-cover transition-transform duration-300 ease-in-out group-hover/item:scale-110'
                />
                <div className='absolute inset-0 bg-gradient-to-t from-black to-transparent bg-opacity-50 transition-opacity duration-300 ease-in-out group-hover/item:bg-opacity-75'>
                    <div className='absolute bottom-0 left-0 right-0 p-2 sm:p-4 transform translate-y-2 transition-transform duration-300 ease-in-out group-hover/item:translate-y-0'>
                        <h3 className='text-white font-bold text-xs lg:text-sm truncate'>
                            {item.title}
                        </h3>
                        <p className='text-white text-xs lg:text-sm opacity-75 group-hover/item:opacity-100 transition-opacity duration-300 ease-in-out'>
                            {genres &&
                                `${genres[0]}${
                                    genres.length > 1 ? `, ${genres[1]}` : ''
                                }`}
                        </p>
                        <div className='flex space-x-2 -mt-4 group-hover/item:mt-2 opacity-0 group-hover/item:opacity-100 transition-all duration-300 ease-in-out transform translate-y-2 group-hover/item:translate-y-0'>
                            <Button
                                size='sm'
                                className='w-full bg-white text-black hover:bg-white/90 text-xs sm:text-sm'
                            >
                                <Link
                                    to={`/watch/${item.id}/episode/${episode_index}`}
                                    className='text-black flex gap-1 items-center'
                                >
                                    <Play className='h-full w-4' />
                                    {' Watch'}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SectionCard;
