import type { FC } from 'react';

import { ChangeEmailAddressSectionCard } from '../ChangeEmailAddressSectionCard/ChangeEmailAddressSectionCard';
import { ChangePasswordSectionCard } from '../ChangePasswordSectionCard';
import { KeysSectionCard } from '../KeysSectionCard/KeysSectionCard';
import { NotificationsSectionCard } from '../NotificationsSectionCard';
import { PreferencesSectionCard } from '../PreferencesSectionCard/PreferencesSectionCard';
import { ProfileSectionCard } from '../ProfileSectionCard';

export const SettingsRoot: FC = () => {
  return (
    <div className="flex flex-col">
      <h1>Settings</h1>

      <div className="flex flex-col gap-4">
        <ProfileSectionCard />
        <NotificationsSectionCard />
        <PreferencesSectionCard />
        <KeysSectionCard />
        <ChangePasswordSectionCard />
        <ChangeEmailAddressSectionCard />
      </div>
    </div>
  );
};
