import * as React from 'react';

import { cn } from '@/utils/cn';

const BaseCard = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div
      ref={ref}
      className={cn(
        'rounded-lg border border-embed-highlight bg-card text-card-foreground shadow-sm bg-embed',
        className,
      )}
      {...props}
    />
  ),
);
BaseCard.displayName = 'BaseCard';

const BaseCardHeader = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div ref={ref} className={cn('flex flex-col space-y-1.5 p-6', className)} {...props} />
  ),
);
BaseCardHeader.displayName = 'CardHeader';

const BaseCardTitle = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLHeadingElement>
>(({ className, ...props }, ref) => (
  <h3
    ref={ref}
    className={cn('text-2xl font-semibold leading-none tracking-tight border-b-0 mb-0', className)}
    {...props}
  />
));
BaseCardTitle.displayName = 'BaseCardTitle';

const BaseCardDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ className, ...props }, ref) => (
  <p ref={ref} className={cn('text-sm text-muted-foreground', className)} {...props} />
));
BaseCardDescription.displayName = 'BaseCardDescription';

const BaseCardContent = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div ref={ref} className={cn('p-6 pt-0', className)} {...props} />
  ),
);
BaseCardContent.displayName = 'BaseCardContent';

const BaseCardFooter = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => (
    <div ref={ref} className={cn('flex items-center p-6 pt-0', className)} {...props} />
  ),
);
BaseCardFooter.displayName = 'BaseCardFooter';

export {
  BaseCard,
  BaseCardContent,
  BaseCardDescription,
  BaseCardFooter,
  BaseCardHeader,
  BaseCardTitle,
};
