import * as React from 'react';

import { cn } from '@/utils/cn';

export type BaseInputProps = React.InputHTMLAttributes<HTMLInputElement>;

const BaseInput = React.forwardRef<HTMLInputElement, BaseInputProps>(
  ({ className, type, ...props }, ref) => {
    return (
      <input
        type={type}
        className={cn(
          'flex h-10 w-full rounded-md border light:border-neutral-200 light:bg-white px-3 py-2 text-sm',
          'light:ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium',
          'light:placeholder:text-neutral-500 focus-visible:outline-none focus-visible:ring-2 light:focus-visible:ring-neutral-950',
          'focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-neutral-800',
          'bg-neutral-950 ring-offset-neutral-950 placeholder:text-neutral-400 focus-visible:ring-neutral-300',
          className,
        )}
        ref={ref}
        {...props}
      />
    );
  },
);
BaseInput.displayName = 'BaseInput';

export { BaseInput };
