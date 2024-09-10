import type { ColumnFiltersState } from '@tanstack/react-table';

export function buildFilterParams(columnFilters: ColumnFiltersState): Record<string, string> {
  const params: Record<string, string> = {};

  for (const columnFilter of columnFilters) {
    const filterKey = `filter[${columnFilter.id}]`;
    const filterValue = (columnFilter.value as unknown[]).join(',');

    params[filterKey] = filterValue;
  }

  return params;
}
