import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-react';
import { Button } from '@/components/ui/button';
import React from 'react';

interface PaginationProps {
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
    className?: string;
}

export default function Pagination({
    currentPage,
    totalPages,
    onPageChange,
    className = '',
}: PaginationProps) {
    const generatePageNumbers = () => {
        const pageNumbers = [];
        const maxVisiblePages = 7;

        if (totalPages <= maxVisiblePages) {
            for (let i = 1; i <= totalPages; i++) {
                pageNumbers.push(i);
            }
        } else {
            if (currentPage <= 3) {
                for (let i = 1; i <= 4; i++) {
                    pageNumbers.push(i);
                }
                pageNumbers.push('...');
                pageNumbers.push(totalPages);
            } else if (currentPage >= totalPages - 2) {
                pageNumbers.push(1);
                pageNumbers.push('...');
                for (let i = totalPages - 3; i <= totalPages; i++) {
                    pageNumbers.push(i);
                }
            } else {
                pageNumbers.push(1);
                pageNumbers.push('...');
                for (let i = currentPage - 1; i <= currentPage + 1; i++) {
                    pageNumbers.push(i);
                }
                pageNumbers.push('...');
                pageNumbers.push(totalPages);
            }
        }

        return pageNumbers;
    };

    return (
        <nav
            role='navigation'
            aria-label='Pagination Navigation'
            className={`flex items-center justify-center space-x-2 ${className}`}
        >
            <Button
                variant='ghost'
                size='icon'
                onClick={() => onPageChange(currentPage - 1)}
                disabled={currentPage === 1}
                aria-label='Go to previous page'
            >
                <ChevronLeft className='h-4 w-4' />
            </Button>
            <div className='flex items-center space-x-1'>
                {generatePageNumbers().map((pageNumber, index) =>
                    pageNumber === '...' ? (
                        <MoreHorizontal
                            key={`ellipsis-${index}`}
                            className='h-4 w-4 text-muted-foreground'
                        />
                    ) : (
                        <Button
                            key={pageNumber}
                            variant={
                                currentPage === pageNumber ? 'default' : 'ghost'
                            }
                            size='icon'
                            onClick={() => onPageChange(pageNumber as number)}
                            aria-label={`Go to page ${pageNumber}`}
                            aria-current={
                                currentPage === pageNumber ? 'page' : undefined
                            }
                        >
                            {pageNumber}
                        </Button>
                    ),
                )}
            </div>
            <Button
                variant='ghost'
                size='icon'
                onClick={() => onPageChange(currentPage + 1)}
                disabled={currentPage === totalPages}
                aria-label='Go to next page'
            >
                <ChevronRight className='h-4 w-4' />
            </Button>
        </nav>
    );
}
