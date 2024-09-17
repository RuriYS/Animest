import React, { useEffect, useState } from 'react';
import Constraint from '../elements/Constraint';
import { Link } from 'react-router-dom';
import { FaCirclePlay } from 'react-icons/fa6';
import { FaInfoCircle } from 'react-icons/fa';
import Button from '../elements/Button';

const FeaturedBanner = () => {
    const [currentIndex, setCurrentIndex] = useState(0);
    const featured = [
        {
            id: 'shikanoko-nokonoko-koshitantan',
            episode: 1,
            title: 'My Deer Friend Nokotan',
            brief: 'Torako Koshi is the epitome of perfection. With her peerless beauty, top-notch grades, and position as student council president, her popularity in school is unrivaled. However, she harbors a dark secret—she was a delinquent back in middle school—and this is something she conceals to the best of her abilities.',
            banner: {
                type: 'video',
                src: '/videos/shikanokoko.mp4',
            },
        },
    ];

    useEffect(() => {
        const switchBanner = () => {
            setCurrentIndex((prev) => (prev + 1) % featured.length);
        };

        const interval = setInterval(switchBanner, 8000);

        return () => clearInterval(interval);
    }, [featured.length]);

    return (
        <div className={'relative h-[60vh] w-full'}>
            {featured[currentIndex].banner.type === 'video' ? (
                <video
                    src={featured[currentIndex].banner.src}
                    autoPlay
                    muted
                    loop
                    className={'object-cover w-full h-full'}
                />
            ) : (
                <img
                    src={featured[currentIndex].banner.src}
                    alt={featured[currentIndex].title}
                    className={'object-cover w-full h-full'}
                />
            )}
            <div
                className={
                    'absolute z-10 h-full content-center -bottom-1 left-0 w-full text-white bg-gradient-to-b from-transparent to-black'
                }
            >
                <Constraint>
                    <div
                        className={
                            'flex space-y-4 flex-col w-[70%] lg:w-[50%] p-4 drop-shadow-lg'
                        }
                    >
                        <h1 className={'text-3xl md:text-5xl font-bold'}>
                            {featured[currentIndex].title}
                        </h1>
                        <p
                            className={
                                'text-xs lg:text-md overflow-hidden text-ellipsis line-clamp-4'
                            }
                        >
                            {featured[currentIndex].brief}
                        </p>
                        <div className='flex space-x-2'>
                            <Link to={`/title/${featured[currentIndex].id}`}>
                                <Button variant='secondary'>
                                    <FaInfoCircle size={16} />
                                    <span>Learn More</span>
                                </Button>
                            </Link>
                            <Link
                                to={`/watch/${featured[currentIndex].id}/episode/1`}
                            >
                                <Button variant='primary'>
                                    <FaCirclePlay size={16} />
                                    <span>Watch</span>
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Constraint>
            </div>
        </div>
    );
};

export default FeaturedBanner;
