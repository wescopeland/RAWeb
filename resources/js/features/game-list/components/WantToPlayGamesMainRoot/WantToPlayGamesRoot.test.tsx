import userEvent from '@testing-library/user-event';
import axios from 'axios';

import { createAuthenticatedUser } from '@/common/models';
import { render, screen, waitFor } from '@/test';
import {
  createGame,
  createGameListEntry,
  createPaginatedData,
  createSystem,
} from '@/test/factories';

import { WantToPlayGamesRoot } from './WantToPlayGamesRoot';

describe('Component: WantToPlayGamesRoot', () => {
  it('renders without crashing', () => {
    // ARRANGE
    const { container } = render<App.Community.Data.UserGameListPageProps>(
      <WantToPlayGamesRoot />,
      {
        pageProps: {
          filterableSystemOptions: [],
          paginatedGameListEntries: createPaginatedData([]),
          can: { develop: false },
        },
      },
    );

    // ASSERT
    expect(container).toBeTruthy();
  });

  it('displays default columns', () => {
    // ARRANGE
    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [],
        paginatedGameListEntries: createPaginatedData([]),
        can: { develop: false },
      },
    });

    // ASSERT
    expect(screen.getByRole('columnheader', { name: /title/i }));
    expect(screen.getByRole('columnheader', { name: /system/i }));
    expect(screen.getByRole('columnheader', { name: /achievements/i }));
    expect(screen.getByRole('columnheader', { name: /points/i }));
    expect(screen.getByRole('columnheader', { name: /rarity/i }));
    expect(screen.getByRole('columnheader', { name: /release date/i }));
  });

  it('shows game rows', () => {
    // ARRANGE
    const mockSystem = createSystem({
      nameShort: 'MD',
      iconUrl: 'https://retroachievements.org/test.png',
    });

    const mockGame = createGame({
      title: 'Sonic the Hedgehog',
      system: mockSystem,
      achievementsPublished: 42,
      pointsTotal: 500,
      pointsWeighted: 1000,
      releasedAt: '2006-08-24T00:56:00+00:00',
      releasedAtGranularity: 'day',
    });

    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [],
        paginatedGameListEntries: createPaginatedData([createGameListEntry({ game: mockGame })]),
        can: { develop: false },
      },
    });

    // ASSERT
    expect(screen.getByRole('cell', { name: /sonic/i })).toBeVisible();
    expect(screen.getByRole('cell', { name: /md/i })).toBeVisible();
    expect(screen.getByRole('cell', { name: '42' })).toBeVisible();
    expect(screen.getByRole('cell', { name: '500 (1,000)' })).toBeVisible();
    expect(screen.getByRole('cell', { name: '×2.00' })).toBeVisible();
    expect(screen.getByRole('cell', { name: 'Aug 24, 2006' })).toBeVisible();
  });

  it('allows users to remove games from their backlog', async () => {
    // ARRANGE
    const deleteSpy = vi.spyOn(axios, 'delete').mockResolvedValueOnce({ success: true });

    const mockSystem = createSystem({
      nameShort: 'MD',
      iconUrl: 'https://retroachievements.org/test.png',
    });

    const mockGame = createGame({
      title: 'Sonic the Hedgehog',
      system: mockSystem,
      achievementsPublished: 42,
      pointsTotal: 500,
      pointsWeighted: 1000,
      releasedAt: '2006-08-24T00:56:00+00:00',
      releasedAtGranularity: 'day',
    });

    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [],
        paginatedGameListEntries: createPaginatedData([createGameListEntry({ game: mockGame })]),
        can: { develop: false },
      },
    });

    // ACT
    await userEvent.click(screen.getByRole('button', { name: /open menu/i }));
    await userEvent.click(screen.getByRole('menuitem', { name: /remove/i }));

    // ASSERT
    expect(deleteSpy).toHaveBeenCalledWith(route('api.user-game-list.destroy', mockGame.id));
  });

  it('allows users to toggle column visibility', async () => {
    // ARRANGE
    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [],
        paginatedGameListEntries: createPaginatedData([]),
        can: { develop: false },
      },
    });

    // ACT
    await userEvent.click(screen.getByRole('button', { name: /view/i }));
    await userEvent.click(screen.getByRole('menuitemcheckbox', { name: /points/i }));

    // ASSERT
    expect(screen.queryByRole('columnheader', { name: /points/i })).not.toBeInTheDocument();
  });

  it('given the user cannot develop achievements, they cannot enable an Open Tickets column', async () => {
    // ARRANGE
    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [],
        paginatedGameListEntries: createPaginatedData([]),
        can: { develop: false },
      },
    });

    // ACT
    await userEvent.click(screen.getByRole('button', { name: /view/i }));

    // ASSERT
    expect(
      screen.queryByRole('menuitemcheckbox', { name: /open tickets/i }),
    ).not.toBeInTheDocument();
  });

  it('given the user can develop achievements, they can enable an Open Tickets column', async () => {
    // ARRANGE
    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [],
        paginatedGameListEntries: createPaginatedData([]),
        can: { develop: true },
      },
    });

    // ACT
    await userEvent.click(screen.getByRole('button', { name: /view/i }));
    await userEvent.click(screen.getByRole('menuitemcheckbox', { name: /open tickets/i }));

    // ASSERT
    expect(screen.getByRole('columnheader', { name: /open tickets/i })).toBeVisible();
  });

  it('given a game row has a non-zero amount of open tickets, the cell links to the tickets page', async () => {
    // ARRANGE
    const mockSystem = createSystem({
      nameShort: 'MD',
      iconUrl: 'https://retroachievements.org/test.png',
    });

    const mockGame = createGame({
      id: 1,
      title: 'Sonic the Hedgehog',
      system: mockSystem,
      achievementsPublished: 42,
      pointsTotal: 500,
      pointsWeighted: 1000,
      releasedAt: '2006-08-24T00:56:00+00:00',
      releasedAtGranularity: 'day',
      numUnresolvedTickets: 2,
    });

    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [],
        paginatedGameListEntries: createPaginatedData([createGameListEntry({ game: mockGame })]),
        can: { develop: true },
      },
    });

    // ACT
    await userEvent.click(screen.getByRole('button', { name: /view/i }));
    await userEvent.click(screen.getByRole('menuitemcheckbox', { name: /open tickets/i }));

    // ASSERT
    expect(screen.getByRole('link', { name: '2' }));
  });

  it('allows the user to search for games on the list', async () => {
    // ARRANGE
    const getSpy = vi.spyOn(axios, 'get').mockResolvedValueOnce({ data: createPaginatedData([]) });

    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [],
        paginatedGameListEntries: createPaginatedData([]),
        can: { develop: false },
      },
    });

    // ACT
    await userEvent.type(screen.getByRole('textbox', { name: /search games/i }), 'dragon quest');

    // ASSERT
    await waitFor(() => {
      expect(getSpy).toHaveBeenCalledWith([
        'api.user-game-list.index',
        {
          'filter[title]': 'dragon quest',
          page: 1,
          sort: 'title',
        },
      ]);
    });
  });

  it('allows the user to filter by system/console', async () => {
    // ARRANGE
    window.HTMLElement.prototype.scrollIntoView = vi.fn();

    const getSpy = vi.spyOn(axios, 'get').mockResolvedValueOnce({ data: createPaginatedData([]) });

    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [createSystem({ id: 1, name: 'Genesis/Mega Drive' })],
        paginatedGameListEntries: createPaginatedData([]),
        can: { develop: false },
      },
    });

    // ACT
    await userEvent.click(screen.getByTestId('filter-System'));
    await userEvent.click(screen.getByRole('option', { name: /genesis/i }));

    // ASSERT
    await waitFor(() => {
      expect(getSpy).toHaveBeenCalledWith([
        'api.user-game-list.index',
        {
          'filter[system]': '1',
          page: 1,
          sort: 'title',
        },
      ]);
    });
  });

  it('allows the user to filter by whether the game has achievements', async () => {
    // ARRANGE
    window.HTMLElement.prototype.scrollIntoView = vi.fn();

    const getSpy = vi.spyOn(axios, 'get').mockResolvedValueOnce({ data: createPaginatedData([]) });

    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [createSystem({ id: 1, name: 'Genesis/Mega Drive' })],
        paginatedGameListEntries: createPaginatedData([]),
        can: { develop: false },
      },
    });

    // ACT
    await userEvent.click(screen.getByTestId('filter-Has achievements'));
    await userEvent.click(screen.getByRole('option', { name: /yes/i }));

    // ASSERT
    await waitFor(() => {
      expect(getSpy).toHaveBeenCalledWith([
        'api.user-game-list.index',
        {
          'filter[achievementsPublished]': 'has',
          page: 1,
          sort: 'title',
        },
      ]);
    });
  });

  it('always displays the number of total games', () => {
    // ARRANGE
    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [createSystem({ id: 1, name: 'Genesis/Mega Drive' })],
        paginatedGameListEntries: createPaginatedData([], { total: 300 }),
        can: { develop: false },
      },
    });

    // ASSERT
    expect(screen.getByText(/300 games/i)).toBeVisible();
  });

  it('given there are multiple pages, allows the user to advance to the next page', async () => {
    // ARRANGE
    const getSpy = vi.spyOn(axios, 'get').mockResolvedValueOnce({ data: createPaginatedData([]) });

    render<App.Community.Data.UserGameListPageProps>(<WantToPlayGamesRoot />, {
      pageProps: {
        auth: { user: createAuthenticatedUser() },
        filterableSystemOptions: [createSystem({ id: 1, name: 'Genesis/Mega Drive' })],
        paginatedGameListEntries: createPaginatedData([createGameListEntry()], {
          total: 300,
          currentPage: 1,
          perPage: 1,
        }),
        can: { develop: false },
      },
    });

    // ACT
    await userEvent.click(screen.getByRole('button', { name: /next page/i }));

    // ASSERT
    await waitFor(() => {
      expect(getSpy).toHaveBeenCalledWith([
        'api.user-game-list.index',
        {
          page: 2,
          sort: 'title',
        },
      ]);
    });
  });
});
