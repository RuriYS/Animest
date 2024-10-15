import { useState, useRef, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Play, Info, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';

interface Props {
    id: string;
    title: string;
    image: string;
    year: string;
}

interface MovieDetails {
    summary: string;
    genres: string[];
    backgroundVideo?: string;
}

interface Result {
    description: string;
    genres: {
        id: string;
        name: string;
    }[];
}

export default function SearchResult({ id, title, image, year }: Props) {
    const navigate = useNavigate();
    const [isExpanded, setIsExpanded] = useState(false);
    const [details, setDetails] = useState<MovieDetails | null>(null);
    const timeoutRef = useRef<NodeJS.Timeout | null>(null);
    const cardRef = useRef<HTMLDivElement>(null);
    const [position, setPosition] = useState({ x: 0, y: 0 });

    const fetchDetails = async () => {
        const { data } = await axios.get(`/api/titles/${id}`);
        if (data.message) {
            const result: Result = data.message.result;
            const genres = result.genres
                ? result.genres.map((v, _) => v.name)
                : [];
            setDetails({
                summary: result.description as string,
                genres: genres,
            });
        }
    };

    const handleExpand = () => {
        if (!isExpanded && !details) {
            fetchDetails();
        }
        setIsExpanded(true);
    };

    const handleCollapse = () => {
        setIsExpanded(false);
    };

    const handleMouseEnter = () => {
        timeoutRef.current = setTimeout(handleExpand, 1000);
    };

    const handleMouseLeave = () => {
        if (timeoutRef.current) {
            clearTimeout(timeoutRef.current);
        }
        if (isExpanded) {
            handleCollapse();
        }
    };

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                cardRef.current &&
                !cardRef.current.contains(event.target as Node)
            ) {
                handleCollapse();
            }
        };

        document.addEventListener('mousedown', handleClickOutside);

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    useEffect(() => {
        const updatePosition = () => {
            if (cardRef.current) {
                const rect = cardRef.current.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const cardWidth = rect.width;
                const expandedWidth = cardWidth * 1.25;

                let xOffset = 0;

                if (rect.left < expandedWidth / 2) {
                    xOffset = 0;
                } else if (rect.right + expandedWidth > viewportWidth) {
                    xOffset = -50;
                } else {
                    xOffset = -(expandedWidth - cardWidth) / 2;
                }
                setPosition({ x: xOffset, y: -20 });
            }
        };

        updatePosition();
        window.addEventListener('resize', updatePosition);

        return () => {
            window.removeEventListener('resize', updatePosition);
        };
    }, [isExpanded]);

    return (
        <div className='relative w-full' style={{ aspectRatio: '2 / 3' }}>
            <motion.div
                ref={cardRef}
                className='absolute inset-0 overflow-hidden rounded-lg transition-all duration-300 ease-in-out hover:shadow-xl cursor-pointer'
                style={{
                    width: isExpanded ? '125%' : '100%',
                    height: isExpanded ? '125%' : '100%',
                    zIndex: isExpanded ? 10 : 1,
                    x: isExpanded ? position.x : 0,
                    y: isExpanded ? position.y : 0,
                    scale: isExpanded ? 1.1 : 1,
                }}
                animate={{}}
                transition={{ duration: 0.3 }}
                onClick={() => navigate(`/watch/${id}`)}
                onMouseEnter={handleMouseEnter}
                onMouseLeave={handleMouseLeave}
            >
                <motion.img
                    src={image}
                    alt={title}
                    className='absolute inset-0 w-full h-full object-cover transition-opacity duration-300'
                    style={{ opacity: isExpanded ? 0.3 : 1 }}
                />
                <motion.div
                    className='absolute inset-0 bg-gradient-to-t from-black to-transparent'
                    style={{ opacity: isExpanded ? 1 : 0.7 }}
                />
                <motion.div className='relative p-0 flex flex-col h-full'>
                    <motion.h3
                        className={`${
                            isExpanded && 'hidden'
                        } absolute flex flex-col gap-2 bottom-0 p-4 shadow-lg`}
                    >
                        <span className='text-base font-bold text-foreground line-clamp-3'>
                            {title}
                        </span>
                        <span className='text-sm text-muted-foreground'>
                            {year}
                        </span>
                    </motion.h3>

                    <AnimatePresence>
                        {isExpanded && details && (
                            <motion.div
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                transition={{ duration: 0.5 }}
                                className={`absolute inset-0 bg-neutral-900 rounded-lg overflow-hidden`}
                            >
                                <div className='relative w-full h-full'>
                                    <AnimatePresence>
                                        <motion.div
                                            key={`${id}-details-bg`}
                                            initial={{ opacity: 0 }}
                                            animate={{ opacity: 1 }}
                                            transition={{ duration: 0.5 }}
                                        >
                                            {details &&
                                            details.backgroundVideo ? (
                                                <video
                                                    src={
                                                        details.backgroundVideo
                                                    }
                                                    autoPlay
                                                    loop
                                                    muted
                                                    className='absolute inset-0 w-full h-full object-cover opacity-50'
                                                />
                                            ) : (
                                                <img
                                                    src={image}
                                                    alt={title}
                                                    className='absolute inset-0 w-full h-full object-cover opacity-50'
                                                />
                                            )}
                                        </motion.div>
                                        <div className='absolute inset-0 bg-gradient-to-t from-black via-black/70 to-transparent' />
                                        <div className='relative z-10 p-6 h-full flex flex-col'>
                                            <Button
                                                variant='ghost'
                                                size='icon'
                                                className='absolute top-2 right-2 text-white hover:bg-white/20'
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    handleCollapse();
                                                }}
                                            >
                                                <X className='h-4 w-4' />
                                            </Button>
                                            <h2 className='text-2xl font-bold text-white mb-2 line-clamp-3'>
                                                {title}
                                            </h2>
                                            <p className='text-lg text-gray-300 mb-3'>
                                                {year}
                                            </p>
                                            <motion.div
                                                key={`${id}-details-summary`}
                                                initial={{ opacity: 0 }}
                                                animate={{ opacity: 1 }}
                                                transition={{ duration: 0.5 }}
                                            >
                                                <p className='text-sm text-white mb-3 line-clamp-4'>
                                                    {details && details.summary}
                                                </p>
                                            </motion.div>
                                            <motion.div
                                                key={`${id}-details-genres`}
                                                initial={{ opacity: 0 }}
                                                animate={{ opacity: 1 }}
                                                transition={{ duration: 0.5 }}
                                            >
                                                {details.genres.length > 0 && (
                                                    <p className='text-xs text-gray-300 mb-3'>
                                                        Genres:{' '}
                                                        {details &&
                                                            details.genres.join(
                                                                ', ',
                                                            )}
                                                    </p>
                                                )}
                                            </motion.div>
                                            <div className='flex space-x-3 mb-4'>
                                                <Button
                                                    size='default'
                                                    className='flex items-center space-x-2'
                                                >
                                                    <Play
                                                        className='w-4 h-4'
                                                        color='black'
                                                    />
                                                    <Link
                                                        to={`/watch/${id}/episode/1`}
                                                        className='text-sm text-black'
                                                    >
                                                        Play
                                                    </Link>
                                                </Button>
                                                <Button
                                                    size='default'
                                                    variant='outline'
                                                    className='flex items-center space-x-2 border-[1px] border-gray-600'
                                                >
                                                    <Info
                                                        className='w-4 h-4'
                                                        color='white'
                                                    />
                                                    <span className='text-sm'>
                                                        More Info
                                                    </span>
                                                </Button>
                                            </div>
                                        </div>
                                    </AnimatePresence>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </motion.div>
            </motion.div>
        </div>
    );
}
