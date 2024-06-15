import type { FC } from 'react';

import { useCardTooltip } from '@/common/hooks/useCardTooltip';

interface UserAvatarProps {
  displayName: string | null;

  hasTooltip?: boolean;
}

export const UserAvatar: FC<UserAvatarProps> = ({ displayName, hasTooltip = true }) => {
  const { cardTooltipProps } = useCardTooltip({ dynamicType: 'user', dynamicId: displayName });

  return (
    <a
      href={displayName ? route('user.show', [displayName]) : undefined}
      className="flex items-center gap-2"
      {...(hasTooltip && displayName ? cardTooltipProps : undefined)}
    >
      <img
        loading="lazy"
        decoding="async"
        width="24"
        height="24"
        src={`http://media.retroachievements.org/UserPic/${displayName}.png`}
        alt={displayName ?? 'Deleted User'}
        className="rounded-sm"
      />

      <span>{displayName}</span>
    </a>
  );
};
