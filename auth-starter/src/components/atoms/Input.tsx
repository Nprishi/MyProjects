import React, { forwardRef } from 'react';
import { cn } from '@utils/cn';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {}

const Input = forwardRef<HTMLInputElement, InputProps>(({className, ...props}, ref) => (
  <input ref={ref} className={cn('h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2', className)} {...props} />
));

Input.displayName = 'Input';
export default Input;