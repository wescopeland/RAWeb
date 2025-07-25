import { render, screen } from '@/test';
import {
  createAchievementSetClaim,
  createAggregateAchievementSetCredits,
  createUserCredits,
} from '@/test/factories';

import { AchievementSetCredits } from './AchievementSetCredits';

// Shallow render the children.
vi.mock('./AchievementAuthorsDisplay', () => ({
  AchievementAuthorsDisplay: vi.fn(() => <div data-testid="achievement-authors-display" />),
}));
vi.mock('./ClaimantsDisplay', () => ({
  ClaimantsDisplay: vi.fn(() => <div data-testid="claimants-display" />),
}));
vi.mock('./ArtworkCreditsDisplay', () => ({
  ArtworkCreditsDisplay: vi.fn(() => <div data-testid="artwork-credits-display" />),
}));
vi.mock('./CodeCreditsDisplay', () => ({
  CodeCreditsDisplay: vi.fn(() => <div data-testid="code-credits-display" />),
}));
vi.mock('./DesignCreditsDisplay', () => ({
  DesignCreditsDisplay: vi.fn(() => <div data-testid="design-credits-display" />),
}));

describe('Component: AchievementSetCredits', () => {
  it('renders without crashing', () => {
    // ARRANGE
    const { container } = render(<AchievementSetCredits />, {
      pageProps: {
        achievementSetClaims: [],
        aggregateCredits: createAggregateAchievementSetCredits(),
      },
    });

    // ASSERT
    expect(container).toBeTruthy();
  });

  it('given there are no credits and no claims, displays nothing', () => {
    // ARRANGE
    render(<AchievementSetCredits />, {
      pageProps: {
        achievementSetClaims: [],
        aggregateCredits: createAggregateAchievementSetCredits(),
      },
    });

    // ASSERT
    expect(screen.queryByTestId('set-credits')).not.toBeInTheDocument();
    expect(screen.queryByTestId('achievement-authors-display')).not.toBeInTheDocument();
  });

  it('given artwork credits exist, shows the artwork credits display', () => {
    // ARRANGE
    const aggregateCredits = createAggregateAchievementSetCredits({
      achievementSetArtwork: [createUserCredits({ displayName: 'Alice' })],
      achievementsArtwork: [createUserCredits({ displayName: 'Bob' })],
      achievementsLogic: [],
      achievementsMaintainers: [],
      achievementsDesign: [],
      achievementsTesting: [],
      achievementsWriting: [],
      hashCompatibilityTesting: [],
    });

    render(<AchievementSetCredits />, {
      pageProps: { achievementSetClaims: [], aggregateCredits },
    });

    // ASSERT
    expect(screen.getByTestId('artwork-credits-display')).toBeVisible();
  });

  it('given coding credits exist, shows the code credits display', () => {
    // ARRANGE
    const aggregateCredits = createAggregateAchievementSetCredits({
      achievementsLogic: [createUserCredits({ displayName: 'Charlie' })],
      achievementsMaintainers: [createUserCredits({ displayName: 'David' })],
      achievementsDesign: [],
      achievementsTesting: [],
      achievementsWriting: [],
      hashCompatibilityTesting: [],
    });

    render(<AchievementSetCredits />, {
      pageProps: { achievementSetClaims: [], aggregateCredits },
    });

    // ASSERT
    expect(screen.getByTestId('code-credits-display')).toBeVisible();
  });

  it('given design credits exist, shows the design credits display', () => {
    // ARRANGE
    const aggregateCredits = createAggregateAchievementSetCredits({
      achievementsDesign: [createUserCredits({ displayName: 'Eve' })],
      achievementsTesting: [createUserCredits({ displayName: 'Frank' })],
      achievementsWriting: [createUserCredits({ displayName: 'Grace' })],
      hashCompatibilityTesting: [],
    });

    render(<AchievementSetCredits />, {
      pageProps: { achievementSetClaims: [], aggregateCredits },
    });

    // ASSERT
    expect(screen.getByTestId('design-credits-display')).toBeVisible();
  });

  it('given duplicate users in artwork credits, deduplicates them before deciding to show the component', () => {
    // ARRANGE
    const sharedUser = createUserCredits({ displayName: 'Alice' });
    const aggregateCredits = createAggregateAchievementSetCredits({
      achievementSetArtwork: [sharedUser],
      achievementsArtwork: [sharedUser], // !! same user in both arrays
      achievementsLogic: [],
      achievementsMaintainers: [],
      achievementsDesign: [],
      achievementsTesting: [],
      achievementsWriting: [],
      hashCompatibilityTesting: [],
    });

    render(<AchievementSetCredits />, {
      pageProps: { achievementSetClaims: [], aggregateCredits },
    });

    // ASSERT
    // ... component should still show because there's at least one unique user ...
    expect(screen.getByTestId('artwork-credits-display')).toBeVisible();
  });

  it('given all logic users are already authors, does not show the code credits display', () => {
    // ARRANGE
    const author = createUserCredits({ displayName: 'Alice' });
    const aggregateCredits = createAggregateAchievementSetCredits({
      achievementsAuthors: [author],
      achievementsLogic: [author], // !! same user is both author and logic credit
      achievementsMaintainers: [], // !! no maintainers
      achievementsDesign: [],
      achievementsTesting: [],
      achievementsWriting: [],
      hashCompatibilityTesting: [],
    });

    render(<AchievementSetCredits />, {
      pageProps: { achievementSetClaims: [], aggregateCredits },
    });

    // ASSERT
    // ... code credits should not show because filtered logic credits is empty and no maintainers ...
    expect(screen.queryByTestId('code-credits-display')).not.toBeInTheDocument();
  });

  it('given active claims, shows claimants', () => {
    // ARRANGE
    const aggregateCredits = {
      achievementsAuthors: [],
      achievementSetArtwork: [],
      achievementsArtwork: [],
      achievementsLogic: [],
      achievementsMaintainers: [],
      achievementsDesign: [],
      achievementsTesting: [],
      achievementsWriting: [],
      hashCompatibilityTesting: [],
    };

    render(<AchievementSetCredits />, {
      pageProps: {
        aggregateCredits,
        achievementSetClaims: [createAchievementSetClaim()],
      },
    });

    // ASSERT
    expect(screen.getByTestId('claimants-display')).toBeVisible();
  });

  it('given all credit types have users, shows all displays', () => {
    // ARRANGE
    const aggregateCredits = createAggregateAchievementSetCredits({
      achievementsAuthors: [createUserCredits({ displayName: 'Author1' })],
      achievementSetArtwork: [createUserCredits({ displayName: 'Artist1' })],
      achievementsArtwork: [],
      achievementsLogic: [createUserCredits({ displayName: 'Logic1' })],
      achievementsMaintainers: [],
      achievementsDesign: [createUserCredits({ displayName: 'Designer1' })],
      achievementsTesting: [],
      achievementsWriting: [],
      hashCompatibilityTesting: [],
    });

    render(<AchievementSetCredits />, {
      pageProps: { achievementSetClaims: [], aggregateCredits },
    });

    // ASSERT
    expect(screen.getByTestId('achievement-authors-display')).toBeVisible();
    expect(screen.getByTestId('artwork-credits-display')).toBeVisible();
    expect(screen.getByTestId('code-credits-display')).toBeVisible();
    expect(screen.getByTestId('design-credits-display')).toBeVisible();
  });

  it('given hash compatibility testing credits exist, shows the design credits display', () => {
    // ARRANGE
    const aggregateCredits = {
      achievementsAuthors: [],
      achievementSetArtwork: [],
      achievementsArtwork: [],
      achievementsLogic: [],
      achievementsMaintainers: [],
      achievementsDesign: [],
      achievementsTesting: [],
      achievementsWriting: [],
      hashCompatibilityTesting: [createUserCredits({ displayName: 'HashTester1' })], // !!
    };

    render(<AchievementSetCredits />, { pageProps: { aggregateCredits } });

    // ASSERT
    expect(screen.getByTestId('design-credits-display')).toBeVisible();
  });
});
