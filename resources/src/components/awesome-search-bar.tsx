import React, { useState } from 'react';
import { Search, Sliders } from 'lucide-react';
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

export function AwesomeSearchBar() {
    const [advancedSearch, setAdvancedSearch] = useState(false);

    return (
        <div className='w-full max-w-3xl mx-auto p-4 space-y-4'>
            <div className='flex space-x-2'>
                <div className='flex-grow'>
                    <Input
                        type='text'
                        placeholder='Search...'
                        className='w-full'
                    />
                </div>
                <Select defaultValue='all'>
                    <SelectTrigger className='w-[180px]'>
                        <SelectValue placeholder='Category' />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value='all'>All Categories</SelectItem>
                        <SelectItem value='products'>Products</SelectItem>
                        <SelectItem value='services'>Services</SelectItem>
                        <SelectItem value='blogs'>Blogs</SelectItem>
                    </SelectContent>
                </Select>
                <Popover>
                    <PopoverTrigger asChild>
                        <Button
                            variant='outline'
                            size='icon'
                            className='shrink-0'
                        >
                            <Sliders className='h-4 w-4' />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent className='w-80'>
                        <div className='grid gap-4'>
                            <div className='space-y-2'>
                                <h4 className='font-medium leading-none'>
                                    Advanced Search
                                </h4>
                                <p className='text-sm text-muted-foreground'>
                                    Configure your search parameters
                                </p>
                            </div>
                            <div className='grid gap-2'>
                                <div className='flex items-center space-x-2'>
                                    <Switch
                                        id='exact-match'
                                        checked={advancedSearch}
                                        onCheckedChange={setAdvancedSearch}
                                    />
                                    <Label htmlFor='exact-match'>
                                        Exact match
                                    </Label>
                                </div>
                                <div className='flex items-center space-x-2'>
                                    <Switch id='include-archived' />
                                    <Label htmlFor='include-archived'>
                                        Include archived
                                    </Label>
                                </div>
                                <div className='flex items-center space-x-2'>
                                    <Switch id='search-titles-only' />
                                    <Label htmlFor='search-titles-only'>
                                        Search titles only
                                    </Label>
                                </div>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>
                <Button type='submit' className='shrink-0'>
                    <Search className='h-4 w-4 mr-2' />
                    Search
                </Button>
            </div>
            {advancedSearch && (
                <div className='text-sm text-muted-foreground'>
                    Advanced search is enabled. Your search will look for exact
                    matches.
                </div>
            )}
        </div>
    );
}
