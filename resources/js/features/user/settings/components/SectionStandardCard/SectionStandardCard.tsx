import type { FC, ReactNode } from 'react';

import {
  BaseCard,
  BaseCardContent,
  BaseCardHeader,
  BaseCardTitle,
} from '@/common/components/+vendor/BaseCard';

interface SectionStandardCardProps {
  title: string;
  children: ReactNode;
}

export const SectionStandardCard: FC<SectionStandardCardProps> = ({ title, children }) => {
  return (
    <BaseCard className="w-full">
      <BaseCardHeader className="pb-4">
        <BaseCardTitle>{title}</BaseCardTitle>
      </BaseCardHeader>

      <BaseCardContent>{children}</BaseCardContent>
    </BaseCard>
  );
};
