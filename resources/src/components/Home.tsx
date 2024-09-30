import React from 'react';

import Section from '@/components/Section';
import FeaturedBanner from '@/components/FeaturedBanner';

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
