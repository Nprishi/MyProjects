import React, { forwardRef } from 'react';
import { cn } from '@utils/cn';

const Card = forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(({className, ...props}, ref) => (
  <div ref={ref} className={cn('rounded-lg border bg-card p-6 shadow-sm', className)} {...props} />
));

Card.displayName = 'Card';
export default Card;