import React from 'react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { SortMode } from 'types';

interface SortSelectProps {
    onSortChange: (value: SortMode) => void;
}

const SortSelect: React.FC<SortSelectProps> = ({ onSortChange }) => {
    return (
        <Select onValueChange={onSortChange}>
            <SelectTrigger className='w-[180px] outline-none border-[1px] border-gray-500 hover:border-gray-300 focus:ring-1 focus:ring-gray-400 transition-colors'>
                <SelectValue placeholder='Sort' />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value='newest'>Newest First</SelectItem>
                <SelectItem value='oldest'>Oldest First</SelectItem>
                <SelectItem value='index-asc'>Episode (Ascending)</SelectItem>
                <SelectItem value='index-desc'>Episode (Descending)</SelectItem>
            </SelectContent>
        </Select>
    );
};

export default SortSelect;
