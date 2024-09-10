import type { Column } from '@tanstack/react-table';
import type { HTMLAttributes, ReactNode } from 'react';
import { RxArrowDown, RxArrowUp, RxCaretSort, RxEyeNone } from 'react-icons/rx';

import { BaseButton } from '@/common/components/+vendor/BaseButton';
import {
  BaseDropdownMenu,
  BaseDropdownMenuContent,
  BaseDropdownMenuItem,
  BaseDropdownMenuSeparator,
  BaseDropdownMenuTrigger,
} from '@/common/components/+vendor/BaseDropdownMenu';
import { cn } from '@/utils/cn';

interface DataTableColumnHeaderProps<TData, TValue> extends HTMLAttributes<HTMLDivElement> {
  column: Column<TData, TValue>;

  ascLabel?: string;
  descLabel?: string;
  sortLabelVariant?: 'asc-desc' | 'more-less';
}

export function DataTableColumnHeader<TData, TValue>({
  className,
  column,
  sortLabelVariant = 'asc-desc',
}: DataTableColumnHeaderProps<TData, TValue>): ReactNode {
  if (!column.getCanSort()) {
    return <div className={cn(className)}>{column.columnDef.meta?.label}</div>;
  }

  return (
    <div
      className={cn(
        'flex items-center space-x-2',
        column.columnDef.meta?.align === 'right' ? 'justify-end' : '',
        column.columnDef.meta?.align === 'center' ? 'justify-center' : '',
        className,
      )}
    >
      <BaseDropdownMenu>
        <BaseDropdownMenuTrigger asChild>
          <BaseButton
            variant="ghost"
            size="sm"
            className="data-[state=open]:bg-accent -ml-3 h-8 !transform-none focus-visible:!ring-0 focus-visible:!ring-offset-0"
          >
            <span>{column.columnDef.meta?.label}</span>

            {column.getIsSorted() === 'desc' ? (
              <RxArrowDown className="ml-2 h-4 w-4" />
            ) : column.getIsSorted() === 'asc' ? (
              <RxArrowUp className="ml-2 h-4 w-4" />
            ) : (
              <RxCaretSort className="ml-2 h-4 w-4" />
            )}
          </BaseButton>
        </BaseDropdownMenuTrigger>

        <BaseDropdownMenuContent align="start">
          {sortLabelVariant === 'asc-desc' ? (
            <>
              <BaseDropdownMenuItem onClick={() => column.toggleSorting(false)}>
                <RxArrowUp className="text-muted-foreground/70 mr-2 h-3.5 w-3.5" />
                Asc
              </BaseDropdownMenuItem>

              <BaseDropdownMenuItem onClick={() => column.toggleSorting(true)}>
                <RxArrowDown className="text-muted-foreground/70 mr-2 h-3.5 w-3.5" />
                Desc
              </BaseDropdownMenuItem>
            </>
          ) : null}

          {sortLabelVariant === 'more-less' ? (
            <>
              <BaseDropdownMenuItem onClick={() => column.toggleSorting(true)}>
                <RxArrowUp className="text-muted-foreground/70 mr-2 h-3.5 w-3.5" />
                More
              </BaseDropdownMenuItem>

              <BaseDropdownMenuItem onClick={() => column.toggleSorting(false)}>
                <RxArrowDown className="text-muted-foreground/70 mr-2 h-3.5 w-3.5" />
                Less
              </BaseDropdownMenuItem>
            </>
          ) : null}

          {column.getCanHide() ? (
            <>
              <BaseDropdownMenuSeparator />

              <BaseDropdownMenuItem onClick={() => column.toggleVisibility(false)}>
                <RxEyeNone className="text-muted-foreground/70 mr-2 h-3.5 w-3.5" />
                Hide
              </BaseDropdownMenuItem>
            </>
          ) : null}
        </BaseDropdownMenuContent>
      </BaseDropdownMenu>
    </div>
  );
}
