import type {
  ColumnFiltersState,
  PaginationState,
  SortingState,
  VisibilityState,
} from '@tanstack/react-table';
import { flexRender, getCoreRowModel, useReactTable } from '@tanstack/react-table';
import { useMemo, useState } from 'react';

import {
  BaseTable,
  BaseTableBody,
  BaseTableCell,
  BaseTableHead,
  BaseTableHeader,
  BaseTableRow,
} from '@/common/components/+vendor/BaseTable';
import { useGameListQuery } from '@/common/hooks/useGameListQuery';
import { usePageProps } from '@/common/hooks/usePageProps';
import { cn } from '@/utils/cn';

import { buildColumnDefinitions } from './buildColumnDefinitions';
import { DataTablePagination } from './DataTablePagination';
import { WantToPlayGamesDataTableToolbar } from './WantToPlayGamesDataTableToolbar';

export const WantToPlayGamesDataTable = () => {
  const { paginatedGameListEntries, can } =
    usePageProps<App.Community.Data.UserGameListPageProps>();

  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex: paginatedGameListEntries.currentPage - 1,
    pageSize: paginatedGameListEntries.perPage,
  });

  const [sorting, setSorting] = useState<SortingState>([{ id: 'title', desc: false }]);

  const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({
    lastUpdated: false,
    numVisibleLeaderboards: false,
    numUnresolvedTickets: false,
  });

  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);

  const gameListQuery = useGameListQuery({ columnFilters, pagination, sorting });

  const table = useReactTable({
    columns: useMemo(
      () => buildColumnDefinitions({ canSeeOpenTicketsColumn: can.develop ?? false }),
      [can.develop],
    ),
    data: gameListQuery.data?.items ?? [],
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
    rowCount: gameListQuery.data?.total,
    pageCount: gameListQuery.data?.lastPage,
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
      <WantToPlayGamesDataTableToolbar table={table} />

      <BaseTable containerClassName="overflow-auto rounded-md border border-neutral-700 bg-embed light:border-neutral-300 lg:overflow-visible lg:rounded-sm">
        <BaseTableHeader>
          {table.getHeaderGroups().map((headerGroup) => (
            <BaseTableRow
              key={headerGroup.id}
              className="do-not-highlight bg-embed lg:sticky lg:top-[41px] lg:z-10"
            >
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
              <BaseTableCell
                colSpan={table.getAllColumns().length}
                className="h-24 bg-embed text-center"
              >
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
