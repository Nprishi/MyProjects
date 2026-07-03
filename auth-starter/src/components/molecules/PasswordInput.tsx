import React, { useState, forwardRef } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import Input from '../atoms/Input';
import Label from '../atoms/Label';

interface PasswordInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  required?: boolean;
}

const PasswordInput = forwardRef<HTMLInputElement, PasswordInputProps>(({label, error, required, ...props}, ref) => {
  const [show, setShow] = useState(false);
  return (
    <div className="space-y-2">
      {label && <Label required={required}>{label}</Label>}
      <div className="relative">
        <Input ref={ref} type={show ? 'text' : 'password'} {...props} />
        <button type="button" onClick={() => setShow(!show)} className="absolute right-3 top-1/2 -translate-y-1/2">
          {show ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
        </button>
      </div>
      {error && <p className="text-sm text-destructive">{error}</p>}
    </div>
  );
});

PasswordInput.displayName = 'PasswordInput';
export default PasswordInput;