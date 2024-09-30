import moment from 'moment';
import React from 'react';

export const getMoment = (uploadDate?: string): [string, string] => {
    if (!uploadDate) return ['', ''];
    const [dateNum, ...dateSuffixes] = moment(uploadDate).fromNow().split(' ');
    const suffix = dateSuffixes.join(' ');
    return [dateNum, suffix];
};

const Moment: React.FC<
    { uploadDate?: string } & React.HTMLAttributes<HTMLParagraphElement>
> = ({ uploadDate, className, ...props }) => {
    const [dateNum, dateSuffix] = getMoment(uploadDate);
    return (
        <p className={`flex gap-1 ${className}`} {...props}>
            <span>{dateNum}</span>
            <span>{dateSuffix}</span>
        </p>
    );
};

export default Moment;
