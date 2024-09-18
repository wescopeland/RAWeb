import type { Table } from '@tanstack/react-table';
import { useEffect, useState } from 'react';
import { useDebounce } from 'react-use';

import { BaseInput } from '@/common/components/+vendor/BaseInput';

interface DataTableSearchInputProps<TData> {
  table: Table<TData>;
}

export function DataTableSearchInput<TData>({ table }: DataTableSearchInputProps<TData>) {
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
      table.getColumn('title')?.setFilterValue(rawInputValue);
    },
    200,
    [rawInputValue],
  );

  return (
    <BaseInput
      placeholder="Filter games..."
      value={rawInputValue}
      onChange={(event) => setRawInputValue(event.target.value)}
      className="h-8 w-[150px] lg:w-[250px]"
    />
  );
}
