import type { ColumnFiltersState, SortingState } from '@tanstack/react-table';
import { getCoreRowModel, useReactTable } from '@tanstack/react-table';
import userEvent from '@testing-library/user-event';
import type { FC } from 'react';
import type { RouteName } from 'ziggy-js';

import { createAuthenticatedUser } from '@/common/models';
import { buildAchievementsPublishedColumnDef } from '@/features/game-list/utils/column-definitions/buildAchievementsPublishedColumnDef';
import { buildSystemColumnDef } from '@/features/game-list/utils/column-definitions/buildSystemColumnDef';
import { buildTitleColumnDef } from '@/features/game-list/utils/column-definitions/buildTitleColumnDef';
import i18n from '@/i18n-client';
import { render, screen, waitFor } from '@/test';
import { createSystem, createZiggyProps } from '@/test/factories';

import { DataTableSuperFilter } from './DataTableSuperFilter';

vi.mock('@/common/components/GameAvatar', () => ({ GameAvatar: () => null }));

// Suppress vaul a11y warnings.
console.warn = vi.fn();

// Suppress "[Table] Column with id 'progress' does not exist".
console.error = vi.fn();

interface TestHarnessProps {
  columnFilters?: ColumnFiltersState;
  hasResults?: boolean;
  onColumnFiltersChange?: (filters: ColumnFiltersState) => void;
  onSortingChange?: (sorting: SortingState) => void;
  sorting?: SortingState;
}

// We need to instantiate props with a hook, so a test harness is required.
const TestHarness: FC<TestHarnessProps> = ({
  columnFilters = [],
  hasResults = false,
  onColumnFiltersChange = () => {},
  onSortingChange = () => {},
  sorting = [],
}) => {
  const table = useReactTable({
    onColumnFiltersChange: onColumnFiltersChange as any,
    onSortingChange: onSortingChange as any,
    columns: [
      buildTitleColumnDef({ t_label: i18n.t('Title') }),
      buildSystemColumnDef({ t_label: i18n.t('System') }),
      buildAchievementsPublishedColumnDef({ t_label: i18n.t('Achievements') }),
    ],
    data: [],
    rowCount: 0,
    getCoreRowModel: getCoreRowModel(),
    state: {
      columnFilters,
      sorting,
    },
  });

  return <DataTableSuperFilter table={table} hasResults={hasResults} />;
};

describe('Component: DataTableSuperFilter', () => {
  beforeEach(() => {
    window.HTMLElement.prototype.hasPointerCapture = vi.fn();
    window.HTMLElement.prototype.scrollIntoView = vi.fn();
    window.HTMLElement.prototype.setPointerCapture = vi.fn();

    // This prevents vaul from exploding.
    vi.spyOn(window, 'getComputedStyle').mockReturnValue({
      transform: 'matrix(1, 0, 0, 1, 0, 0)',
      getPropertyValue: vi.fn(),
    } as unknown as CSSStyleDeclaration);
  });

  it('renders without crashing', () => {
    // ARRANGE
    const { container } = render(<TestHarness />);

    // ASSERT
    expect(container).toBeTruthy();
  });

  describe('Button Label', () => {
    it('given the achievementsPublished filter value is "has" and there is no systems filter, displays the correct label', () => {
      // ARRANGE
      render(<TestHarness columnFilters={[{ id: 'achievementsPublished', value: 'has' }]} />);

      // ASSERT
      expect(screen.getByRole('button', { name: 'Playable, All Systems' })).toBeVisible();
    });

    it('given the achievementsPublished filter value is "none" and there is no systems filter, displays the correct label', () => {
      // ARRANGE
      render(<TestHarness columnFilters={[{ id: 'achievementsPublished', value: 'none' }]} />);

      // ASSERT
      expect(screen.getByRole('button', { name: 'Not Playable, All Systems' })).toBeVisible();
    });

    it('given the achievementsPublished filter value is not set and there is no systems filter, displays the correct label', () => {
      // ARRANGE
      render(<TestHarness />);

      // ASSERT
      expect(screen.getByRole('button', { name: 'All Games, All Systems' })).toBeVisible();
    });

    it('given a single system filter is set, displays the correct label', () => {
      // ARRANGE
      render(
        <TestHarness
          columnFilters={[
            { id: 'achievementsPublished', value: 'has' },
            { id: 'system', value: [5] },
          ]}
        />,
      );

      // ASSERT
      expect(screen.getByRole('button', { name: 'Playable, 1 System' })).toBeVisible();
    });

    it('given multiple system filters are set, displays the correct label', () => {
      // ARRANGE
      render(
        <TestHarness
          columnFilters={[
            { id: 'achievementsPublished', value: 'has' },
            { id: 'system', value: [5, 7] },
          ]}
        />,
      );

      // ASSERT
      expect(screen.getByRole('button', { name: 'Playable, 2 Systems' })).toBeVisible();
    });
  });

  describe('Drawer', () => {
    it('given the user taps the super filter button, the drawer appears', async () => {
      // ARRANGE
      render(<TestHarness columnFilters={[{ id: 'achievementsPublished', value: 'has' }]} />, {
        pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
      });

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      // ASSERT
      expect(screen.getByRole('dialog', { name: /customize view/i })).toBeVisible();
    });

    it('allows the user to set the achievements published filter value', async () => {
      // ARRANGE
      const onColumnFiltersChange = vi.fn();

      render(
        <TestHarness
          columnFilters={[{ id: 'achievementsPublished', value: 'has' }]}
          onColumnFiltersChange={onColumnFiltersChange}
        />,
        {
          pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      await userEvent.click(screen.getByRole('combobox', { name: /has achievements/i }));
      await userEvent.click(screen.getByRole('option', { name: 'No' }));

      // ASSERT
      const updateFn = onColumnFiltersChange.mock.calls[0][0];
      const newFilters = updateFn([{ id: 'achievementsPublished', value: 'has' }]);

      expect(newFilters).toEqual([{ id: 'achievementsPublished', value: ['none'] }]);
    });

    it('allows the user to set the systems filter value', async () => {
      // ARRANGE
      const onColumnFiltersChange = vi.fn();

      render<{ filterableSystemOptions: App.Platform.Data.System[] }>(
        <TestHarness
          columnFilters={[{ id: 'achievementsPublished', value: 'has' }]}
          onColumnFiltersChange={onColumnFiltersChange}
        />,
        {
          pageProps: {
            ziggy: createZiggyProps({ device: 'mobile' }),
            filterableSystemOptions: [
              createSystem({ id: 1, name: 'NES/Famicom' }),
              createSystem({ id: 2, name: 'Nintendo 64' }),
            ],
          },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      await userEvent.click(screen.getByRole('option', { name: 'NES/Famicom' }));

      // ASSERT
      const updateFn = onColumnFiltersChange.mock.calls[0][0];
      const newFilters = updateFn([{ id: 'achievementsPublished', value: 'has' }]);

      expect(newFilters).toEqual([
        { id: 'achievementsPublished', value: 'has' },
        { id: 'system', value: ['1'] },
      ]);
    });

    it('given there is only one filterable system option, does not show the system filter', async () => {
      // ARRANGE
      const onColumnFiltersChange = vi.fn();

      render<{ filterableSystemOptions: App.Platform.Data.System[] }>(
        <TestHarness
          columnFilters={[{ id: 'achievementsPublished', value: 'has' }]}
          onColumnFiltersChange={onColumnFiltersChange}
        />,
        {
          pageProps: {
            ziggy: createZiggyProps({ device: 'mobile' }),
            filterableSystemOptions: [createSystem({ id: 1, name: 'NES/Famicom' })],
          },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      // ASSERT
      expect(screen.queryByRole('option', { name: 'NES/Famicom' })).not.toBeInTheDocument();
    });

    it('allows the user to change the current sort order to an ascending sort', async () => {
      // ARRANGE
      const onSortingChange = vi.fn();

      render(
        <TestHarness
          columnFilters={[{ id: 'achievementsPublished', value: 'has' }]}
          onSortingChange={onSortingChange}
        />,
        {
          pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      await userEvent.click(screen.getByRole('combobox', { name: /sort/i }));
      await userEvent.click(screen.getAllByRole('option', { name: /achievements/i })[0]);

      // ASSERT
      const updateFn = onSortingChange.mock.calls[0][0];
      const newSort = updateFn();

      expect(newSort).toEqual([{ id: 'achievementsPublished', desc: true }]);
    });

    it('allows the user to change the current sort order to a descending sort', async () => {
      // ARRANGE
      const onSortingChange = vi.fn();

      render(
        <TestHarness
          columnFilters={[{ id: 'achievementsPublished', value: 'has' }]}
          onSortingChange={onSortingChange}
        />,
        {
          pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      await userEvent.click(screen.getByRole('combobox', { name: /sort/i }));
      await userEvent.click(screen.getAllByRole('option', { name: /achievements/i })[1]);

      // ASSERT
      const updateFn = onSortingChange.mock.calls[0][0];
      const newSort = updateFn();

      expect(newSort).toEqual([{ id: 'achievementsPublished', desc: false }]);
    });

    it('dispatches a tracking action on sort order change', async () => {
      // ARRANGE
      const mockPlausible = vi.fn();

      Object.defineProperty(window, 'plausible', {
        writable: true,
        value: mockPlausible,
      });

      const onSortingChange = vi.fn();

      render(
        <TestHarness
          columnFilters={[{ id: 'achievementsPublished', value: 'has' }]}
          onSortingChange={onSortingChange}
        />,
        {
          pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      await userEvent.click(screen.getByRole('combobox', { name: /sort/i }));
      await userEvent.click(screen.getAllByRole('option', { name: /achievements/i })[0]);

      // ASSERT
      expect(mockPlausible).toHaveBeenCalledOnce();
      expect(mockPlausible).toHaveBeenCalledWith('Game List Sort', {
        props: { order: '-achievementsPublished' },
      });
    });

    it('given there are no game results, disables the random game button', async () => {
      // ARRANGE
      render(
        <TestHarness
          columnFilters={[{ id: 'achievementsPublished', value: 'has' }]}
          hasResults={false}
        />,
        {
          pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      // ASSERT
      expect(screen.getByRole('button', { name: /surprise me/i })).toBeDisabled();
    });

    it('given there are game results, enables the random game button', async () => {
      // ARRANGE
      render(
        <TestHarness
          columnFilters={[{ id: 'achievementsPublished', value: 'has' }]}
          hasResults={true}
        />,
        {
          pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /playable/i }));

      // ASSERT
      expect(screen.getByRole('button', { name: /surprise me/i })).toBeEnabled();
    });
  });

  describe('Sort State Handling', () => {
    it('given no sorting state exists, uses a fallback "title" sort', async () => {
      // ARRANGE
      render(<TestHarness sorting={[]} />, {
        pageProps: { ziggy: createZiggyProps({ device: 'mobile' }), filterableSystemOptions: [] },
      });

      // ACT
      await userEvent.click(screen.getByRole('button'));
      const sortSelect = screen.getByRole('combobox', { name: /sort/i });
      await userEvent.click(sortSelect);

      // ASSERT
      expect(screen.getAllByText('Title, Ascending (A - Z)')[0]).toBeVisible();
    });

    it('given there is an active sort state with multiple sorts, shows the first sort as selected', async () => {
      // ARRANGE
      render(
        <TestHarness
          sorting={[
            { id: 'title', desc: true },
            { id: 'system', desc: false },
          ]}
        />,
        {
          pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
        },
      );

      // ACT
      await userEvent.click(screen.getByRole('button'));
      await userEvent.click(screen.getByRole('combobox', { name: /sort/i }));

      // ASSERT
      expect(screen.getByRole('option', { name: /title.*descending/i })).toBeVisible();
    });

    it('given the user is a guest, does not display the progress filter select', async () => {
      // ARRANGE
      render(<TestHarness />, {
        pageProps: {
          auth: null,
          ziggy: createZiggyProps({ device: 'mobile' }),
        },
      });

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /all games/i }));

      // ASSERT
      expect(screen.queryByRole('combobox', { name: /progress/i })).not.toBeInTheDocument();
    });

    it('given the user is authenticated, displays the progress filter select', async () => {
      // ARRANGE
      render(<TestHarness />, {
        pageProps: {
          auth: {
            user: createAuthenticatedUser(),
          },
          ziggy: createZiggyProps({ device: 'mobile' }),
        },
      });

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /all games/i }));

      // ASSERT
      await waitFor(() => {
        expect(screen.getByRole('combobox', { name: /progress/i })).toBeVisible();
      });
    });
  });

  describe('Set Request Route Specific Behavior', () => {
    const TestHarnessWithRoute: FC<TestHarnessProps & { tableApiRouteName?: RouteName }> = ({
      tableApiRouteName,
      columnFilters = [],
      hasResults = false,
      onColumnFiltersChange = () => {},
      onSortingChange = () => {},
      sorting = [],
    }) => {
      const table = useReactTable({
        onColumnFiltersChange: onColumnFiltersChange as any,
        onSortingChange: onSortingChange as any,
        columns: [
          buildTitleColumnDef({ t_label: i18n.t('Title') }),
          buildSystemColumnDef({ t_label: i18n.t('System') }),
          buildAchievementsPublishedColumnDef({ t_label: i18n.t('Achievements') }),
        ],
        data: [],
        rowCount: 0,
        getCoreRowModel: getCoreRowModel(),
        state: {
          columnFilters,
          sorting,
        },
      });

      return (
        <DataTableSuperFilter
          table={table}
          hasResults={hasResults}
          tableApiRouteName={tableApiRouteName}
        />
      );
    };

    it('given we are on the set-request route, hides game type and set type filters but shows the claimed filter', async () => {
      // ARRANGE
      render(<TestHarnessWithRoute tableApiRouteName="api.set-request.index" />, {
        pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
      });

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /all games/i }));

      // ASSERT
      expect(screen.queryByRole('combobox', { name: /game type/i })).not.toBeInTheDocument();
      expect(screen.queryByRole('combobox', { name: /set type/i })).not.toBeInTheDocument();

      expect(screen.getByRole('combobox', { name: /claimed/i })).toBeVisible();

      expect(screen.queryByRole('combobox', { name: /has achievements/i })).not.toBeInTheDocument();
    });

    it('given we are not on set-request route, shows game type and set type filters but not the claimed filter', async () => {
      // ARRANGE
      render(<TestHarnessWithRoute tableApiRouteName="api.game.index" />, {
        pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
      });

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /all games/i }));

      // ASSERT
      expect(screen.getByRole('combobox', { name: /game type/i })).toBeVisible();
      expect(screen.getByRole('combobox', { name: /set type/i })).toBeVisible();

      expect(screen.queryByRole('combobox', { name: /claimed/i })).not.toBeInTheDocument();

      expect(screen.getByRole('combobox', { name: /has achievements/i })).toBeVisible();
    });

    it('given we are on the set-request route and user is authenticated, does not show the progress filter', async () => {
      // ARRANGE
      render(<TestHarnessWithRoute tableApiRouteName="api.set-request.index" />, {
        pageProps: {
          auth: { user: createAuthenticatedUser() },
          ziggy: createZiggyProps({ device: 'mobile' }),
        },
      });

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /all games/i }));

      // ASSERT
      // ... we are auth'd and it still doesn't appear ...
      expect(screen.queryByRole('combobox', { name: /progress/i })).not.toBeInTheDocument();
    });

    it('given we are on the user-specific set-request route, shows the requests status filter', async () => {
      // ARRANGE
      render(<TestHarnessWithRoute tableApiRouteName="api.set-request.user" />, {
        pageProps: { ziggy: createZiggyProps({ device: 'mobile' }) },
      });

      // ACT
      await userEvent.click(screen.getByRole('button', { name: /all games/i }));

      // ASSERT
      expect(screen.getByRole('combobox', { name: /requests/i })).toBeVisible();
      expect(screen.queryByRole('combobox', { name: /has achievements/i })).not.toBeInTheDocument();
    });
  });
});
