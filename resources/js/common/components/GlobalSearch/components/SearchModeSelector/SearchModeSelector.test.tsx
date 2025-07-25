import userEvent from '@testing-library/user-event';

import { render, screen, waitFor } from '@/test';

import { SearchModeSelector } from './SearchModeSelector';

describe('Component: SearchModeSelector', () => {
  it('renders without crashing', () => {
    // ARRANGE
    const mockOnChange = vi.fn();

    const { container } = render(
      <SearchModeSelector onChange={mockOnChange} rawQuery="" selectedMode="all" />,
    );

    // ASSERT
    expect(container).toBeTruthy();
  });

  it('displays all search mode options', async () => {
    // ARRANGE
    const mockOnChange = vi.fn();

    render(<SearchModeSelector onChange={mockOnChange} rawQuery="" selectedMode="all" />);

    // ASSERT
    await waitFor(() => expect(screen.getByText(/all/i)).toBeVisible());
    await waitFor(() => expect(screen.getByText(/games/i)).toBeVisible());
    await waitFor(() => expect(screen.getByText(/hubs/i)).toBeVisible());
    await waitFor(() => expect(screen.getByText(/users/i)).toBeVisible());
    await waitFor(() => expect(screen.getByText(/achievements/i)).toBeVisible());
  });

  it('given a selected mode, marks the correct chip as selected', () => {
    // ARRANGE
    const mockOnChange = vi.fn();

    render(<SearchModeSelector onChange={mockOnChange} rawQuery="" selectedMode="games" />);

    // ACT
    const gamesButton = screen.getByRole('button', { name: /games/i });
    const allButton = screen.getByRole('button', { name: /all/i });
    const hubsButton = screen.getByRole('button', { name: /hubs/i });

    // ASSERT
    expect(gamesButton).toHaveAttribute('aria-pressed', 'true');
    expect(allButton).toHaveAttribute('aria-pressed', 'false');
    expect(hubsButton).toHaveAttribute('aria-pressed', 'false');
  });

  it('given the user clicks on a chip, calls onChange with the correct value', async () => {
    // ARRANGE
    const mockOnChange = vi.fn();

    render(<SearchModeSelector onChange={mockOnChange} rawQuery="" selectedMode="all" />);

    // ACT
    await userEvent.click(screen.getByRole('button', { name: /games/i }));

    // ASSERT
    expect(mockOnChange).toHaveBeenCalledWith('games');
    expect(mockOnChange).toHaveBeenCalledTimes(1);
  });

  it('given the user clicks on different chips, calls onChange with the appropriate values', async () => {
    // ARRANGE
    const mockOnChange = vi.fn();

    render(<SearchModeSelector onChange={mockOnChange} rawQuery="" selectedMode="all" />);

    // ACT
    await userEvent.click(screen.getByRole('button', { name: /hubs/i }));
    await userEvent.click(screen.getByRole('button', { name: /users/i }));
    await userEvent.click(screen.getByRole('button', { name: /achievements/i }));
    await userEvent.click(screen.getByRole('button', { name: /events/i }));
    await userEvent.click(screen.getByRole('button', { name: /all/i }));

    // ASSERT
    expect(mockOnChange).toHaveBeenNthCalledWith(1, 'hubs');
    expect(mockOnChange).toHaveBeenNthCalledWith(2, 'users');
    expect(mockOnChange).toHaveBeenNthCalledWith(3, 'achievements');
    expect(mockOnChange).toHaveBeenNthCalledWith(4, 'events');
    expect(mockOnChange).toHaveBeenNthCalledWith(5, 'all');
    expect(mockOnChange).toHaveBeenCalledTimes(5);
  });

  it('renders all chips as accessible buttons', () => {
    // ARRANGE
    const mockOnChange = vi.fn();
    render(<SearchModeSelector onChange={mockOnChange} rawQuery="" selectedMode="all" />);

    // ACT
    const buttons = screen.getAllByRole('button');

    // ASSERT
    expect(buttons).toHaveLength(6);
  });

  it('given a raw query string, renders the correct "Browse" url', () => {
    // ARRANGE
    const mockOnChange = vi.fn();

    render(<SearchModeSelector onChange={mockOnChange} rawQuery="mario" selectedMode="all" />);

    // ASSERT
    const browseLink = screen.getByRole('link', { name: /browse/i });

    expect(browseLink).toHaveAttribute('href', expect.stringContaining('mario'));
  });
});
