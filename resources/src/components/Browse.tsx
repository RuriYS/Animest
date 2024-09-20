import { Constraint, ContentContainer, SearchResult } from '@/elements';
import { Loader2, Search, Sliders } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import React, { useCallback, useEffect, useRef, useState } from 'react';
import tw, { styled } from 'twin.macro';
import axios from 'axios';
import { debounce } from 'lodash';

const ResultContainer = styled.div`
    ${tw`p-0 md:p-4`}
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
    position: relative;

    @media (min-width: 768px) {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
`;

interface Result {
    id: string;
    image: string;
    title: string;
    year: string;
}

export default function Component() {
    const [exactMatch, setExactMatch] = useState(false);
    const [searchResults, setSearchResults] = useState<Result[]>([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [sortType, setSortType] = useState<
        'title_az' | 'recently_added' | 'recently_updated' | 'release' | string
    >('title_az');
    const [loading, setLoading] = useState(true);

    const exactMatchRef = useRef(exactMatch);
    const searchTermRef = useRef(searchTerm);
    const sortTypeRef = useRef(sortType);

    const fetchResults = useCallback(
        debounce(async () => {
            try {
                const { data } = await axios.get(
                    `/api/search?q=${encodeURIComponent(
                        searchTermRef.current,
                    )}&s=${sortTypeRef.current}`,
                );
                let filteredResults = data.results;
                if (exactMatchRef.current) {
                    filteredResults = data.results.filter(
                        (result: Result) =>
                            result.title.toLowerCase() ===
                            searchTermRef.current.toLowerCase(),
                    );
                }

                setSearchResults(filteredResults);
            } catch (error) {
                console.error('Error fetching search results:', error);
                setSearchResults([]);
            }
            setLoading(false);
        }, 300),
        [],
    );

    useEffect(() => {
        setLoading(true);
        searchTermRef.current = searchTerm;
        sortTypeRef.current = sortType;
        exactMatchRef.current = exactMatch;
        fetchResults();
    }, [searchTerm, sortType, exactMatch]);

    return (
        <Constraint>
            <ContentContainer>
                <h1 className='text-xl m-auto'>Browse titles</h1>
                <div className='w-full max-w-3xl mx-auto p-4 space-y-4'>
                    <div className='flex space-x-2'>
                        <div className='flex-grow'>
                            <Input
                                type='text'
                                placeholder='Search...'
                                className='w-full text-[0.8rem] border-gray-700'
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                        </div>
                        <Select value={sortType} onValueChange={setSortType}>
                            <SelectTrigger className='w-[180px] text-[0.8rem] border-gray-700'>
                                <SelectValue placeholder='Category' />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    className='text-[0.8rem]'
                                    value='title_az'
                                >
                                    Name (A-Z)
                                </SelectItem>
                                <SelectItem
                                    className='text-[0.8rem]'
                                    value='recently_added'
                                >
                                    Recently Added
                                </SelectItem>
                                <SelectItem
                                    className='text-[0.8rem]'
                                    value='recently_updated'
                                >
                                    Recently Updated
                                </SelectItem>
                                <SelectItem
                                    className='text-[0.8rem]'
                                    value='release_date'
                                >
                                    Release Date
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button
                                    variant='outline'
                                    size='icon'
                                    className='shrink-0 bg-transparent border-gray-700 hover:bg-neutral-300'
                                >
                                    <Sliders className='h-4 w-4' />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className='w-80 bg-neutral-300'>
                                <div className='grid gap-4'>
                                    <div className='space-y-2'>
                                        <h4 className='text-black font-medium leading-none'>
                                            Advanced Search
                                        </h4>
                                        <p className='text-xs text-muted-foreground'>
                                            Configure your search parameters
                                        </p>
                                    </div>
                                    <div className='grid gap-2'>
                                        <div className='flex items-center space-x-2'>
                                            <Switch
                                                id='exact-match'
                                                checked={exactMatch}
                                                onCheckedChange={setExactMatch}
                                            />
                                            <Label htmlFor='exact-match'>
                                                Exact match
                                            </Label>
                                        </div>
                                    </div>
                                </div>
                            </PopoverContent>
                        </Popover>
                        <Button
                            type='submit'
                            className='shrink-0 bg-neutral-200 hover:bg-neutral-400 text-black'
                            onClick={fetchResults}
                        >
                            <Search className='h-4 w-4 mr-2' />
                            <span className='text-[0.8rem]'>Search</span>
                        </Button>
                    </div>
                    {exactMatch && (
                        <div className='text-sm text-muted-foreground'>
                            Advanced search is enabled. Your search will look
                            for exact matches.
                        </div>
                    )}
                </div>
                {loading ? (
                    <div className='w-full h-full flex flex-col items-center justify-center'>
                        <Loader2 className='h-16 w-16 animate-spin text-primary' />
                        <h2 className='mt-4 text-2xl font-semibold text-foreground'>
                            Searching...
                        </h2>
                        <p className='mt-2 text-muted-foreground'>
                            Getting results
                        </p>
                    </div>
                ) : (
                    <ResultContainer>
                        {searchResults.map(({ image, title, id, year }) => (
                            <SearchResult
                                key={id}
                                image={image}
                                title={title}
                                id={id}
                                year={year}
                            />
                        ))}
                    </ResultContainer>
                )}
            </ContentContainer>
        </Constraint>
    );
}
