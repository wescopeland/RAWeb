import { type FC } from 'react';

import { SectionStandardCard } from '../SectionStandardCard';
import { ManageConnectApiKey } from './ManageConnectApiKey';
import { ManageWebApiKey } from './ManageWebApiKey';

export const KeysSectionCard: FC = () => {
  return (
    <SectionStandardCard title="Keys">
      <div className="flex flex-col gap-8">
        <ManageWebApiKey />
        <ManageConnectApiKey />
      </div>
    </SectionStandardCard>
  );
};
