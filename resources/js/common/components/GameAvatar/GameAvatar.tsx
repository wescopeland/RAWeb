import type { FC } from 'react';

import { useCardTooltip } from '@/common/hooks/useCardTooltip';
import type { AvatarSize } from '@/common/models';

import { GameTitle } from '../GameTitle';

interface GameAvatarProps {
  id: number;

  badgeUrl?: string;
  hasTooltip?: boolean;
  showBadge?: boolean;
  showTitle?: boolean;
  size?: AvatarSize;
  title?: string;
}

export const GameAvatar: FC<GameAvatarProps> = ({
  id,
  badgeUrl,
  showBadge,
  showTitle,
  title,
  size = 32,
  hasTooltip = true,
}) => {
  const { cardTooltipProps } = useCardTooltip({ dynamicType: 'game', dynamicId: id });

  return (
    <a
      href={route('game.show', { game: id })}
      className="flex items-center gap-2"
      {...(hasTooltip ? cardTooltipProps : undefined)}
    >
      {showBadge !== false ? (
        <img
          loading="lazy"
          decoding="async"
          width={size}
          height={size}
          src={badgeUrl}
          alt={title ?? 'Game'}
          className="rounded-sm"
        />
      ) : null}

      {title && showTitle !== false ? <GameTitle title={title} /> : null}
    </a>
  );
};
