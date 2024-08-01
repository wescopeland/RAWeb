import type { FC } from 'react';

import { NotificationsSectionCard } from '../NotificationsSectionCard';
import { ProfileSectionCard } from '../ProfileSectionCard';

export const SettingsRoot: FC = () => {
  return (
    <div className="flex flex-col">
      <h1>Settings</h1>

      <div className="flex flex-col gap-6">
        <ProfileSectionCard />
        <NotificationsSectionCard />
      </div>
    </div>
  );
};
