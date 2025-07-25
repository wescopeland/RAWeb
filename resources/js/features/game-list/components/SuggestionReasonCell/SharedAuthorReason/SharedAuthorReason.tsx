import type { FC } from 'react';
import { Trans } from 'react-i18next';
import { LuWrench } from 'react-icons/lu';
import { route } from 'ziggy-js';

import { BaseChip } from '@/common/components/+vendor/BaseChip';
import { GameAvatar } from '@/common/components/GameAvatar';
import { useCardTooltip } from '@/common/hooks/useCardTooltip';

interface SharedAuthorReasonProps {
  relatedAuthor: App.Data.User;
  relatedGame: App.Platform.Data.Game | null;
}

export const SharedAuthorReason: FC<SharedAuthorReasonProps> = ({ relatedAuthor, relatedGame }) => {
  const { cardTooltipProps } = useCardTooltip({
    dynamicType: 'user',
    dynamicId: relatedAuthor.displayName,
  });

  // eslint-disable-next-line @typescript-eslint/no-unused-vars -- extract ref from cardTooltipProps to avoid React 19 <Trans /> bug
  const { ref, ...cardTooltipPropsWithoutRef } = cardTooltipProps;

  return (
    <BaseChip
      data-testid="shared-author-reason"
      className="flex gap-1.5 py-1 text-neutral-300 light:text-neutral-900 xl:whitespace-nowrap"
    >
      <LuWrench className="size-[18px]" />

      <Trans
        i18nKey={relatedGame ? 'By <1>same developer</1> as' : 'By <1>same developer</1>'}
        components={{
          1: (
            // eslint-disable-next-line jsx-a11y/anchor-has-content -- this is passed in by the consumer
            <a
              href={route('user.show', { user: relatedAuthor.displayName })}
              {...cardTooltipPropsWithoutRef}
            />
          ),
        }}
      />

      {relatedGame ? (
        <GameAvatar {...relatedGame} showLabel={false} size={24} wrapperClassName="inline-block" />
      ) : null}
    </BaseChip>
  );
};
