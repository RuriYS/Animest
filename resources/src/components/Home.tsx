import React from 'react';
import FeaturedBanner from './FeaturedBanner';
import Section from './Section';

const Home = () => {
    return (
        <>
            <FeaturedBanner />
            <Section category='popular' header='Top Airing' maxpage={5} />
        </>
    );
};

export default Home;
