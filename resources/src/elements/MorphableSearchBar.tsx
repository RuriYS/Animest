import styled from '@emotion/styled';
import tw from 'twin.macro';
import { useState, useEffect, useCallback } from 'react';
import React from 'react';
import { FaSearch } from 'react-icons/fa';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { debounce } from 'lodash';

const SearchBarWrapper = styled.div`
    ${tw`relative`}
`;

const SearchIcon = styled.div`
    ${tw`absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none`}
    i {
        ${tw`text-white text-xl`}
    }
`;

const SearchBar = styled.input<{ isExpanded: boolean }>`
    ${tw`px-4 h-8 py-1 w-full bg-neutral-900 text-white rounded-lg outline-none transition-all duration-300 ease-in-out`}

    width: ${({ isExpanded }) => (isExpanded ? '100%' : '40px')};
    padding-left: ${({ isExpanded }) => (isExpanded ? '2rem' : '0.5rem')};
    cursor: ${({ isExpanded }) => (isExpanded ? 'text' : 'pointer')};
    opacity: ${({ isExpanded }) => (isExpanded ? 1 : 0)};

    &:focus {
        ${tw`bg-slate-300 text-black outline-none`}
        box-shadow: none;
    }

    @media (min-width: 768px) {
        width: ${({ isExpanded }) => (isExpanded ? '20rem' : '40px')};
    }
`;

const Dropdown = styled.ul<{ isExpanded: boolean }>`
    opacity: ${({ isExpanded }) => (isExpanded ? 1 : 0)};
    ${tw`absolute left-0 mt-2 w-full bg-gray-300 text-black rounded-lg shadow-lg max-h-60 overflow-auto`}
    li {
        ${tw`w-full inline-flex`}
    }
    a {
        ${tw`px-4 py-2 text-sm w-full cursor-pointer hover:bg-gray-200`}
    }
`;

interface Result {
    id: string;
    title: string;
}

const MorphableSearchBar = () => {
    let mt: NodeJS.Timeout;
    const [isExpanded, setExpanded] = useState(false);
    const [isFocused, setFocus] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [searchResults, setSearchResults] = useState<Result[]>([]);

    const handleBlur = () => {
        setTimeout(() => {
            setExpanded(false);
            setFocus(false);
        }, 800);
    };

    const handleMouseEnter = () => {
        if (mt) clearTimeout(mt);
        setExpanded(true);
    };

    const handleMouseLeave = () => {
        mt = setTimeout(() => {
            !isFocused && setExpanded(false);
        }, 800);
    };

    const fetchResults = useCallback(
        debounce(async (value: string) => {
            try {
                const response = await axios.get(
                    `/api/titles?q=${encodeURIComponent(value)}&limit=20`,
                );
                setSearchResults(response.data.results);
            } catch (error) {
                console.error('Error fetching search results:', error);
                setSearchResults([]);
            }
        }, 300),
        [],
    );

    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        console.log(searchResults);
        const value = e.target.value;
        setSearchTerm(value);

        if (value && value.length > 0) {
            fetchResults(value);
        } else {
            setSearchResults([]);
        }
    };

    return (
        <SearchBarWrapper>
            <SearchIcon>
                <FaSearch />
            </SearchIcon>
            <SearchBar
                placeholder={isExpanded ? 'Do a quick search...' : ''}
                isExpanded={isExpanded}
                onFocus={() => setFocus(true)}
                onBlur={handleBlur}
                onMouseEnter={handleMouseEnter}
                onMouseLeave={handleMouseLeave}
                onChange={handleSearch}
                value={searchTerm}
            />
            {searchResults && (
                <Dropdown isExpanded={isExpanded}>
                    {searchResults.map((result, index) => {
                        console.log(result);
                        return (
                            <li key={index}>
                                <Link
                                    to={`/watch/${result.id}/episode/1`}
                                    className='text-black text-sm'
                                >
                                    {result.title}
                                </Link>
                            </li>
                        );
                    })}
                </Dropdown>
            )}
        </SearchBarWrapper>
    );
};

export default MorphableSearchBar;
