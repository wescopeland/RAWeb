import { dehydrate, HydrationBoundary, QueryClient } from '@tanstack/react-query';
import type {
  ColumnFiltersState,
  PaginationState,
  SortingState,
  VisibilityState,
} from '@tanstack/react-table';
import { type FC, useMemo, useState } from 'react';
import { useUpdateEffect } from 'react-use';

import { UserHeading } from '@/common/components/UserHeading';
import { usePageProps } from '@/common/hooks/usePageProps';
import type { AppGlobalProps } from '@/common/models';

import { WantToPlayGamesDataTable } from '../WantToPlayGamesDataTable';

export const WantToPlayGamesRoot: FC = () => {
  const {
    auth,
    paginatedGameListEntries,
    ziggy: { query },
  } = usePageProps<App.Community.Data.UserGameListPageProps>();

  const [queryClient] = useState(() => new QueryClient());

  const targetPageNumber = paginatedGameListEntries.currentPage - 1;
  const [pagination, setPagination] = useState<PaginationState>({
    pageIndex:
      targetPageNumber > paginatedGameListEntries.lastPage
        ? paginatedGameListEntries.lastPage - 1
        : targetPageNumber,
    pageSize: paginatedGameListEntries.perPage,
  });

  const [sorting, setSorting] = useState<SortingState>(mapQueryParamsToSorting(query));

  const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({
    lastUpdated: false,
    numVisibleLeaderboards: false,
    numUnresolvedTickets: false,
  });

  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>(
    mapQueryParamsToColumnFilters(query),
  );

  useMemo(() => {
    queryClient.setQueryData(
      ['data', pagination, sorting, columnFilters],
      paginatedGameListEntries,
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps -- needed for ssr
  }, [queryClient]);

  useUpdateEffect(() => {
    const searchParams = new URLSearchParams(window.location.search);

    if (pagination.pageIndex > 0) {
      searchParams.set('page[number]', String(pagination.pageIndex + 1));
    } else {
      searchParams.delete('page[number]');
    }

    if (columnFilters.length > 0) {
      for (const columnFilter of columnFilters) {
        if (Array.isArray(columnFilter.value)) {
          searchParams.set(`filter[${columnFilter.id}]`, columnFilter.value.join(','));
        } else {
          searchParams.set(`filter[${columnFilter.id}]`, columnFilter.value as string);
        }
      }
    } else {
      for (const paramKey of searchParams.keys()) {
        if (paramKey.includes('filter[')) {
          searchParams.delete(paramKey);
        }
      }
    }

    const [activeSort] = sorting;
    if (activeSort) {
      if (activeSort.id === 'title' && activeSort.desc === false) {
        searchParams.delete('sort');
      } else {
        searchParams.set('sort', `${activeSort.desc ? '-' : ''}${activeSort.id}`);
      }
    }

    const newUrl = searchParams.size
      ? `${window.location.pathname}?${searchParams.toString()}`
      : window.location.pathname;
    // Should this use pushState? I'm worried about blowing the user's browser history away.
    window.history.replaceState(null, '', newUrl);
  }, [pagination, sorting, columnFilters]);

  if (!auth?.user) {
    return null;
  }

  return (
    <div>
      <div id="pagination-scroll-target" className="scroll-mt-16">
        <UserHeading user={auth.user}>Want to Play Games</UserHeading>
      </div>

      <HydrationBoundary state={dehydrate(queryClient)}>
        <WantToPlayGamesDataTable
          columnFilters={columnFilters}
          columnVisibility={columnVisibility}
          pagination={pagination}
          setColumnFilters={setColumnFilters}
          setColumnVisibility={setColumnVisibility}
          setPagination={setPagination}
          setSorting={setSorting}
          sorting={sorting}
        />
      </HydrationBoundary>
    </div>
  );
};

function mapQueryParamsToColumnFilters(
  query: AppGlobalProps['ziggy']['query'],
): ColumnFiltersState {
  const columnFilters: ColumnFiltersState = [];

  if (!query.filter) {
    return columnFilters;
  }

  for (const [filterKey, filterValue] of Object.entries(query.filter)) {
    columnFilters.push({
      id: filterKey,
      value: filterValue.split(','),
    });
  }

  return columnFilters;
}

function mapQueryParamsToSorting(query: AppGlobalProps['ziggy']['query']): SortingState {
  const sorting: SortingState = [];

  // `sort` is actually part of `query`'s prototype, so we have to be
  // extra explicit in how we check for the presence of the param.
  if (typeof query.sort === 'function') {
    sorting.push({ id: 'title', desc: false });

    return sorting;
  }

  // If it's an array, we must have a sort query param. Process it.
  const sortValue = query.sort;

  if (typeof sortValue === 'string') {
    if (sortValue[0] === '-') {
      const split = sortValue.split('-');
      sorting.push({ id: split[1], desc: true });
    } else {
      sorting.push({ id: sortValue, desc: false });
    }
  } else {
    // TODO
  }

  return sorting;
}
