import type { Table } from '@tanstack/react-table';
import { useEffect, useState } from 'react';
import { useDebounce } from 'react-use';

import { BaseInput } from '@/common/components/+vendor/BaseInput';

interface DataTableSearchInputProps<TData> {
  table: Table<TData>;
}

export function DataTableSearchInput<TData>({ table }: DataTableSearchInputProps<TData>) {
  const [isMounted, setIsMounted] = useState(false);
  const [rawInputValue, setRawInputValue] = useState(
    (table.getColumn('title')?.getFilterValue() as string) ?? '',
  );

  /**
   * Listen for changes with column filter state and stay in sync. Otherwise,
   * when the user presses the "Reset" button to reset all filters, our search
   * value will remain. It needs to be reset too.
   */
  useEffect(() => {
    const filterValue = (table.getColumn('title')?.getFilterValue() as string) ?? '';
    setRawInputValue(filterValue);
    // eslint-disable-next-line react-hooks/exhaustive-deps -- this is a valid dependency array
  }, [table.getState().columnFilters]);

  useDebounce(
    () => {
      if (rawInputValue.length === 0 && !isMounted) {
        setIsMounted(true);

        return;
      }

      if (rawInputValue.length >= 3 || rawInputValue.length === 0) {
        table.getColumn('title')?.setFilterValue(rawInputValue);
      }
    },
    200,
    [rawInputValue],
  );

  return (
    <div className="w-full sm:w-auto">
      <label htmlFor="search-field" className="sr-only">
        Search games
      </label>

      <BaseInput
        id="search-field"
        placeholder="Search games..."
        value={rawInputValue}
        onChange={(event) => setRawInputValue(event.target.value)}
        className="h-8 sm:w-[150px] lg:w-[250px]"
      />
    </div>
  );
}
