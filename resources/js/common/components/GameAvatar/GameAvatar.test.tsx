import { createAuthenticatedUser } from '@/common/models';
import { render, screen } from '@/test';
import { createGame, createSystem } from '@/test/factories';

import { GameAvatar } from './GameAvatar';

describe('Component: GameAvatar', () => {
  it('renders without crashing', () => {
    // ARRANGE
    const { container } = render(<GameAvatar {...createGame()} />);

    // ASSERT
    expect(container).toBeTruthy();
  });

  it('given a game title, shows the game title on the screen', () => {
    // ARRANGE
    const game = createGame();

    render(<GameAvatar {...game} />);

    // ASSERT
    expect(screen.getByText(game.title)).toBeVisible();
  });

  it('given there is no title, still renders successfully', () => {
    // ARRANGE
    const game = createGame({ title: undefined });

    render(<GameAvatar {...game} />);

    // ASSERT
    expect(screen.getByRole('img', { name: /game/i })).toBeVisible();
  });

  it('applies the correct size to the image', () => {
    // ARRANGE
    const game = createGame();

    render(<GameAvatar {...game} size={8} />);

    // ASSERT
    const imgEl = screen.getByRole('img');

    expect(imgEl).toHaveAttribute('width', '8');
    expect(imgEl).toHaveAttribute('height', '8');
  });

  it('adds card tooltip props by default', () => {
    // ARRANGE
    const game = createGame({ id: 1 });

    render(<GameAvatar {...game} />);

    // ASSERT
    const anchorEl = screen.getByRole('link');

    expect(anchorEl).toHaveAttribute(
      'x-data',
      "tooltipComponent($el, {dynamicType: 'game', dynamicId: '1', dynamicContext: 'undefined'})",
    );
    expect(anchorEl).toHaveAttribute('x-on:mouseover', 'showTooltip($event)');
    expect(anchorEl).toHaveAttribute('x-on:mouseleave', 'hideTooltip');
    expect(anchorEl).toHaveAttribute('x-on:mousemove', 'trackMouseMovement($event)');
  });

  it('does not add card tooltip props when `hasTooltip` is false', () => {
    // ARRANGE
    const game = createGame({ id: 1 });

    render(<GameAvatar {...game} hasTooltip={false} />);

    // ASSERT
    const anchorEl = screen.getByRole('link');

    expect(anchorEl).not.toHaveAttribute('x-data');
    expect(anchorEl).not.toHaveAttribute('x-on:mouseover');
    expect(anchorEl).not.toHaveAttribute('x-on:mouseleave');
    expect(anchorEl).not.toHaveAccessibleDescription('x-on:mousemove');
  });

  it('given the user is authenticated, sends their username as dynamicContext (to show progress in the hover card)', () => {
    // ARRANGE
    const game = createGame({ id: 1 });

    render(<GameAvatar {...game} />, {
      pageProps: { auth: { user: createAuthenticatedUser({ displayName: 'Scott' }) } },
    });

    // ASSERT
    const anchorEl = screen.getByRole('link');
    const xDataAttribute = anchorEl.getAttribute('x-data');

    expect(xDataAttribute).toContain(`dynamicContext: 'Scott'`);
  });

  it('can be overriden with a custom username for dynamicContext (to show progress in the hover card)', () => {
    // ARRANGE
    const game = createGame({ id: 1 });

    render(<GameAvatar {...game} showHoverCardProgressForUsername="televandalist" />, {
      pageProps: { auth: { user: createAuthenticatedUser({ displayName: 'Scott' }) } },
    });

    // ASSERT
    const anchorEl = screen.getByRole('link');
    const xDataAttribute = anchorEl.getAttribute('x-data');

    expect(xDataAttribute).toContain(`dynamicContext: 'televandalist'`);
  });

  it('can be configured to not show an image', () => {
    // ARRANGE
    const game = createGame({ id: 1 });

    render(<GameAvatar {...game} showImage={false} />);

    // ASSERT
    expect(screen.queryByRole('img')).not.toBeInTheDocument();
  });

  it('can be configured to display an accessible image with smart glow enabled', () => {
    // ARRANGE
    const game = createGame({ id: 1, title: 'Sonic the Hedgehog' });

    render(<GameAvatar {...game} shouldGlow={true} />);

    // ASSERT
    expect(screen.getByRole('img', { name: /sonic the hedgehog/i })).toBeVisible();
  });

  it('given it is configured to not show an image, ignores the glowable image setting', () => {
    // ARRANGE
    const game = createGame({ id: 1, title: 'Sonic the Hedgehog' });

    render(<GameAvatar {...game} showImage={false} shouldGlow={true} />);

    // ASSERT
    expect(screen.queryByRole('img', { name: /sonic the hedgehog/i })).not.toBeInTheDocument();
  });

  it('given it is configured to show a glowable image, renders an accessible image even if the game title cannot be found', () => {
    // ARRANGE
    const game = createGame({ id: 1, title: undefined });

    render(<GameAvatar {...game} shouldGlow={true} />);

    // ASSERT
    expect(screen.getByRole('img', { name: /game/i })).toBeVisible();
  });

  it('given the shouldLink prop is set to false, does not render as a link', () => {
    // ARRANGE
    const game = createGame({ id: 1, title: 'Sonic the Hedgehog' });

    render(<GameAvatar {...game} shouldLink={false} />);

    // ASSERT
    expect(screen.queryByRole('link')).not.toBeInTheDocument();
    expect(screen.getByText(/sonic the hedgehog/i)).toBeVisible();
  });

  it('given showSystemInTitle is true, includes the system name in the game title', () => {
    // ARRANGE
    const system = createSystem({ id: 1, name: 'Sega Genesis', nameShort: 'MD' });
    const game = createGame({ system, title: 'Sonic the Hedgehog' });

    render(<GameAvatar {...game} showSystemInTitle={true} />);

    // ASSERT
    expect(screen.getByText('Sonic the Hedgehog (Sega Genesis)')).toBeVisible();
  });

  it('given the variant is base, applies correct classes', () => {
    // ARRANGE
    const game = createGame();

    render(<GameAvatar {...game} variant="base" />);

    // ASSERT
    const wrapperEl = screen.getByRole('link');
    expect(wrapperEl).toHaveClass('flex', 'max-w-fit', 'items-center', 'gap-2');
  });

  it('given the variant is inline, applies correct classes', () => {
    // ARRANGE
    const game = createGame();

    render(<GameAvatar {...game} variant="inline" />);

    // ASSERT
    const wrapperEl = screen.getByRole('link');
    expect(wrapperEl).toHaveClass('ml-0.5', 'mt-0.5', 'inline-block', 'min-h-7', 'gap-2');
    expect(screen.getByRole('img')).toHaveClass('mr-1.5');
  });

  it('given a custom href, uses it', () => {
    // ARRANGE
    const game = createGame();

    render(<GameAvatar {...game} href="https://google.com" />);

    // ASSERT
    const wrapperEl = screen.getByRole('link');
    expect(wrapperEl).toHaveAttribute('href', 'https://google.com');
  });

  it('given showSystemChip is true, renders the system chip', () => {
    // ARRANGE
    const system = createSystem({ id: 1, nameShort: 'PS1' });
    const game = createGame({ system, title: 'Final Fantasy VII' });

    render(<GameAvatar {...game} showSystemChip={true} />);

    // ASSERT
    expect(screen.getByText(/final fantasy vii/i)).toBeVisible();
    expect(screen.getByText(/ps1/i)).toBeVisible();
  });

  it('given showLabel is false but showSystemChip is true, renders only the system chip', () => {
    // ARRANGE
    const system = createSystem({ id: 1, nameShort: 'NES' });
    const game = createGame({ system, title: 'Super Mario Bros.' });

    render(<GameAvatar {...game} showLabel={false} showSystemChip={true} />);

    // ASSERT
    expect(screen.queryByText(/super mario bros/i)).not.toBeInTheDocument();
    expect(screen.getByText(/nes/i)).toBeVisible();
  });
});
