import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type {
  ColumnFiltersState,
  PaginationState,
  SortingState,
  VisibilityState,
} from '@tanstack/react-table';
import { flexRender, getCoreRowModel, useReactTable } from '@tanstack/react-table';
import axios from 'axios';
import { useState } from 'react';

import {
  BaseTable,
  BaseTableBody,
  BaseTableCell,
  BaseTableHead,
  BaseTableHeader,
  BaseTableRow,
} from '@/common/components/+vendor/BaseTable';
import { usePageProps } from '@/common/hooks/usePageProps';
import { cn } from '@/utils/cn';

import { buildFilterParams } from './buildFilterParams';
import { buildSortParam } from './buildSortParam';
import { columns } from './columnDefinitions';
import { DataTableFacetedFilter } from './DataTableFacetedFilter';
import { DataTablePagination } from './DataTablePagination';
import { DataTableViewOptions } from './DataTableViewOptions';

export const WantToPlayGamesDataTable = () => {
  const { paginatedGameListEntries, filterableSystemOptions } =
    usePageProps<App.Community.Data.UserGameListPageProps>();

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: paginatedGameListEntries.currentPage - 1,
    pageSize: paginatedGameListEntries.perPage,
  });

  const [sorting, setSorting] = useState<SortingState>([{ id: 'title', desc: false }]);

  const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({
    lastUpdated: true, // set to false
    numVisibleLeaderboards: true, // set to false
  });

  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);

  const dataQuery = useQuery<App.Data.PaginatedData<App.Community.Data.UserGameListEntry>>({
    queryKey: ['data', pagination, sorting, columnFilters],
    staleTime: 1 * 60 * 1000, // 1 minute
    queryFn: async () => {
      const response = await axios.get<
        App.Data.PaginatedData<App.Community.Data.UserGameListEntry>
      >(
        route('api.user-game-list.index', {
          page: pagination.pageIndex + 1,
          sort: buildSortParam(sorting),
          ...buildFilterParams(columnFilters),
        }),
      );

      return response.data;
    },
    // initialData: paginatedGameListEntries,
    placeholderData: keepPreviousData,
  });

  const table = useReactTable({
    columns,
    data: dataQuery.data?.items ?? [],
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
    rowCount: dataQuery.data?.total,
    pageCount: dataQuery.data?.lastPage,
    onColumnVisibilityChange: setColumnVisibility,
    onColumnFiltersChange: (updateOrValue) => {
      table.setPageIndex(0);

      setColumnFilters(updateOrValue);
    },
    onPaginationChange: (newPaginationValue) => {
      setPagination(newPaginationValue);
    },
    onSortingChange: (newSortingValue) => {
      table.setPageIndex(0);

      setSorting(newSortingValue);
    },
    getCoreRowModel: getCoreRowModel(),
    state: { columnFilters, columnVisibility, pagination, sorting },
  });

  return (
    <div className="flex flex-col gap-3">
      <div className="flex w-full justify-between">
        <div>
          <DataTableFacetedFilter
            column={table.getColumn('system')}
            title="System"
            options={filterableSystemOptions
              .sort((a, b) => a.name.localeCompare(b.name))
              .map((system) => ({ label: system.name, value: String(system.id) }))}
          />
        </div>

        <DataTableViewOptions table={table} />
      </div>

      <BaseTable containerClassName="rounded-md bg-embed border-neutral-700 border">
        <BaseTableHeader>
          {table.getHeaderGroups().map((headerGroup) => (
            <BaseTableRow key={headerGroup.id} className="do-not-highlight">
              {headerGroup.headers.map((header) => {
                return (
                  <BaseTableHead key={header.id}>
                    {header.isPlaceholder
                      ? null
                      : flexRender(header.column.columnDef.header, header.getContext())}
                  </BaseTableHead>
                );
              })}
            </BaseTableRow>
          ))}
        </BaseTableHeader>

        <BaseTableBody>
          {table.getRowModel().rows?.length ? (
            table.getRowModel().rows.map((row) => (
              <BaseTableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                {row.getVisibleCells().map((cell) => (
                  <BaseTableCell
                    key={cell.id}
                    className={cn(
                      cell.column.columnDef.meta?.align === 'right' ? 'pr-6 text-right' : '',
                      cell.column.columnDef.meta?.align === 'center' ? 'text-center' : '',
                    )}
                  >
                    {flexRender(cell.column.columnDef.cell, cell.getContext())}
                  </BaseTableCell>
                ))}
              </BaseTableRow>
            ))
          ) : (
            <BaseTableRow>
              <BaseTableCell colSpan={columns.length} className="h-24 text-center">
                No results.
              </BaseTableCell>
            </BaseTableRow>
          )}
        </BaseTableBody>
      </BaseTable>

      <DataTablePagination table={table} />
    </div>
  );
};
