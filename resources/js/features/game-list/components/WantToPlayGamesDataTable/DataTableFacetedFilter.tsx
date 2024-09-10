import type { Column } from '@tanstack/react-table';
import { RxCheck, RxPlusCircled } from 'react-icons/rx';

import { BaseBadge } from '@/common/components/+vendor/BaseBadge';
import { BaseButton } from '@/common/components/+vendor/BaseButton';
import {
  BaseCommand,
  BaseCommandEmpty,
  BaseCommandGroup,
  BaseCommandInput,
  BaseCommandItem,
  BaseCommandList,
  BaseCommandSeparator,
} from '@/common/components/+vendor/BaseCommand';
import {
  BasePopover,
  BasePopoverContent,
  BasePopoverTrigger,
} from '@/common/components/+vendor/BasePopover';
import { BaseSeparator } from '@/common/components/+vendor/BaseSeparator';
import { cn } from '@/utils/cn';

interface DataTableFacetedFilterProps<TData, TValue> {
  options: Array<{
    label: string;
    value: string;
    icon?: React.ComponentType<{ className?: string }>;
  }>;

  column?: Column<TData, TValue>;
  title?: string;
}

export function DataTableFacetedFilter<TData, TValue>({
  options,
  column,
  title,
}: DataTableFacetedFilterProps<TData, TValue>) {
  const facets = column?.getFacetedUniqueValues();
  const selectedValues = new Set(column?.getFilterValue() as string[]);

  return (
    <BasePopover>
      <BasePopoverTrigger asChild>
        <BaseButton size="sm" className="border-dashed">
          <RxPlusCircled className="mr-2 h-4 w-4" />

          {title}

          {selectedValues?.size > 0 ? (
            <>
              <BaseSeparator orientation="vertical" className="mx-2 h-4" />

              <BaseBadge variant="secondary" className="rounded-sm px-1 font-normal lg:hidden">
                {selectedValues.size}
              </BaseBadge>

              <div className="hidden space-x-1 lg:flex">
                {selectedValues.size > 2 ? (
                  <BaseBadge variant="secondary" className="rounded-sm px-1 font-normal">
                    {selectedValues.size} selected
                  </BaseBadge>
                ) : (
                  <>
                    {options
                      .filter((option) => selectedValues.has(option.value))
                      .map((option) => (
                        <BaseBadge
                          variant="secondary"
                          key={option.value}
                          className="rounded-sm px-1 font-normal"
                        >
                          {option.label}
                        </BaseBadge>
                      ))}
                  </>
                )}
              </div>
            </>
          ) : null}
        </BaseButton>
      </BasePopoverTrigger>

      <BasePopoverContent className="min-w-[200px] p-0" align="start">
        <BaseCommand>
          <BaseCommandInput placeholder={title} />

          <BaseCommandList>
            <BaseCommandEmpty>
              <span className="text-muted">No options found.</span>
            </BaseCommandEmpty>

            <BaseCommandGroup>
              {options.map((option) => {
                const isSelected = selectedValues.has(option.value);

                return (
                  <BaseCommandItem
                    key={option.value}
                    onSelect={() => {
                      if (isSelected) {
                        selectedValues.delete(option.value);
                      } else {
                        selectedValues.add(option.value);
                      }
                      const filterValues = Array.from(selectedValues);
                      column?.setFilterValue(filterValues.length ? filterValues : undefined);
                    }}
                  >
                    <div
                      className={cn(
                        'border-primary mr-2 flex h-4 w-4 items-center justify-center rounded-sm border',
                        isSelected
                          ? 'bg-primary text-neutral-50 light:text-neutral-950'
                          : 'opacity-50 [&_svg]:invisible',
                      )}
                    >
                      <RxCheck className="h-4 w-4" />
                    </div>

                    {option.icon ? (
                      <option.icon className="text-muted-foreground mr-2 h-4 w-4" />
                    ) : null}

                    <span className="lght:text-neutral-900 text-neutral-200">{option.label}</span>

                    {facets?.get(option.value) && (
                      <span className="ml-auto flex h-4 w-4 items-center justify-center font-mono text-xs">
                        {facets.get(option.value)}
                      </span>
                    )}
                  </BaseCommandItem>
                );
              })}
            </BaseCommandGroup>

            {selectedValues.size > 0 ? (
              <div className="sticky bottom-0 bg-neutral-950">
                <BaseCommandSeparator />
                <BaseCommandGroup>
                  <BaseCommandItem
                    onSelect={() => column?.setFilterValue(undefined)}
                    className="cursor-pointer justify-center text-center text-xs text-link transition hover:bg-neutral-900"
                  >
                    Clear filters
                  </BaseCommandItem>
                </BaseCommandGroup>
              </div>
            ) : null}
          </BaseCommandList>
        </BaseCommand>
      </BasePopoverContent>
    </BasePopover>
  );
}
