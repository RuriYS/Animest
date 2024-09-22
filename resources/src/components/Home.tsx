import React from 'react';
import FeaturedBanner from './FeaturedBanner';
import Section from './Section';

const Home = () => {
    return (
        <>
            <title>Home | Animest</title>
            <FeaturedBanner />
            <Section category='popular' header='Top Airing' maxpage={5} />
        </>
    );
};

export default Home;
