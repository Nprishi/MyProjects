import React, { forwardRef } from 'react';
import { cn } from '@utils/cn';

interface LabelProps extends React.LabelHTMLAttributes<HTMLLabelElement> {
  required?: boolean;
}

const Label = forwardRef<HTMLLabelElement, LabelProps>(({className, required, ...props}, ref) => (
  <label ref={ref} className={cn('text-sm font-medium', className)} {...props}>
    {props.children}
    {required && <span className="text-destructive">*</span>}
  </label>
));

Label.displayName = 'Label';
export default Label;