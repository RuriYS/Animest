import { ChevronLeft, ChevronRight, Play } from 'lucide-react';
import { useRef, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import React from 'react';
import axios from 'axios';

import { Constraint } from '@/elements';
import { Button } from '@/components/ui/button';
import SectionCard from '@/elements/SectionCard';

export interface Title {
    id: string;
    title: string;
    thumbnail: string;
    genres: string[];
    latest_episode_id: string;
}

export interface Props {
    header: string;
    category: string;
    maxpage: number | undefined;
}

export default function Section({ category, header, maxpage = 5 }: Props) {
    const [titles, setTitles] = useState<Title[]>([]);
    const scrollContainerRef = useRef<HTMLDivElement>(null);
    const [scrollAmount, setScrollAmount] = useState(0);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchTitles = async () => {
            for (let index = 1; index <= maxpage; index++) {
                const { data } = await axios.get(
                    `/api/ajax/${category}?page=${index}`,
                );
                setTitles((prevTitles) => {
                    const uniqueTitles = data.list.filter(
                        (newTitle: Title) =>
                            !prevTitles.some(
                                (title) => title.id === newTitle.id,
                            ),
                    );
                    return [...prevTitles, ...uniqueTitles];
                });
            }
        };
        fetchTitles();
    }, []);

    useEffect(() => {
        const updateScrollAmount = () => {
            if (scrollContainerRef.current) {
                const containerWidth = scrollContainerRef.current.offsetWidth;
                setScrollAmount(Math.floor(containerWidth * 0.8));
            }
        };

        updateScrollAmount();
        window.addEventListener('resize', updateScrollAmount);
        return () => window.removeEventListener('resize', updateScrollAmount);
    }, []);

    const scroll = (direction: 'left' | 'right') => {
        if (scrollContainerRef.current) {
            const { current } = scrollContainerRef;
            const scrollValue =
                direction === 'left' ? -scrollAmount : scrollAmount;
            current.scrollBy({ left: scrollValue, behavior: 'smooth' });
        }
    };

    const parseEpisodeId = (str: string) => {
        const re = /([^\/]*?)-episode-(\d+)$/.exec(str);
        return [re ? re[1] : null, re ? re[2] : null];
    };

    return (
        <Constraint>
            <div className='w-full -mt-36 z-10 py-8'>
                <div className='mx-auto px-4'>
                    <h2 className='text-lg lg:text-2xl font-bold mb-4'>
                        {header}
                    </h2>
                    <div className='relative group'>
                        <div
                            ref={scrollContainerRef}
                            className='flex space-x-4 overflow-x-auto pb-4 scrollbar-hide'
                            style={{
                                scrollbarWidth: 'none',
                                msOverflowStyle: 'none',
                            }}
                        >
                            {titles &&
                                titles.map((item) => {
                                    const [_, episode_index] = parseEpisodeId(
                                        item.latest_episode_id,
                                    );

                                    const genres = item.genres.map(
                                        (i) =>
                                            i.charAt(0).toUpperCase() +
                                            i.substring(1),
                                    );
                                    return (
                                        <SectionCard
                                            item={item}
                                            genres={genres}
                                            episode_index={episode_index || '1'}
                                        />
                                    );
                                })}
                        </div>
                        <Button
                            variant='ghost'
                            className='absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-75 transition-all duration-300 ease-in-out opacity-0 group-hover:opacity-100 -translate-x-full group-hover:translate-x-0'
                            onClick={() => scroll('left')}
                        >
                            <ChevronLeft className='h-6 w-6' />
                        </Button>
                        <Button
                            variant='ghost'
                            className='absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-75 transition-all duration-300 ease-in-out opacity-0 group-hover:opacity-100 translate-x-full group-hover:translate-x-0'
                            onClick={() => scroll('right')}
                        >
                            <ChevronRight className='h-6 w-6' />
                        </Button>
                    </div>
                </div>
            </div>
        </Constraint>
    );
}
