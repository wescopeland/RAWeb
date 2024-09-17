import type { Table } from '@tanstack/react-table';
import { RxCross2 } from 'react-icons/rx';

import { BaseButton } from '@/common/components/+vendor/BaseButton';
import { usePageProps } from '@/common/hooks/usePageProps';

import { DataTableFacetedFilter } from './DataTableFacetedFilter';
import { DataTableViewOptions } from './DataTableViewOptions';

interface WantToPlayGamesDataTableToolbarProps<TData> {
  table: Table<TData>;
}

export function WantToPlayGamesDataTableToolbar<TData>({
  table,
}: WantToPlayGamesDataTableToolbarProps<TData>) {
  const { filterableSystemOptions } = usePageProps<App.Community.Data.UserGameListPageProps>();

  const isFiltered = table.getState().columnFilters.length > 0;

  return (
    <div className="flex w-full justify-between">
      <div className="flex flex-1 items-center gap-x-2">
        {table.getColumn('system') ? (
          <DataTableFacetedFilter
            column={table.getColumn('system')}
            title="System"
            options={filterableSystemOptions
              .sort((a, b) => a.name.localeCompare(b.name))
              .map((system) => ({ label: system.name, value: String(system.id) }))}
          />
        ) : null}

        {isFiltered ? (
          <BaseButton
            variant="ghost"
            size="sm"
            onClick={() => table.resetColumnFilters()}
            className="border-dashed px-2 text-link lg:px-3"
          >
            Reset <RxCross2 className="ml-2 h-4 w-4" />
          </BaseButton>
        ) : null}
      </div>

      <DataTableViewOptions table={table} />
    </div>
  );
}
