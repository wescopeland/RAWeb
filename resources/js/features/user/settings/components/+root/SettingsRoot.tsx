import { usePage } from '@inertiajs/react';
import type { FC } from 'react';

import type { SettingsPageProps } from '../../models';
import { ChangeEmailAddressSectionCard } from '../ChangeEmailAddressSectionCard/ChangeEmailAddressSectionCard';
import { ChangePasswordSectionCard } from '../ChangePasswordSectionCard';
import { DeleteAccountSectionCard } from '../DeleteAccountSectionCard';
import { KeysSectionCard } from '../KeysSectionCard/KeysSectionCard';
import { NotificationsSectionCard } from '../NotificationsSectionCard';
import { PreferencesSectionCard } from '../PreferencesSectionCard/PreferencesSectionCard';
import { ProfileSectionCard } from '../ProfileSectionCard';
import { ResetGameProgressSectionCard } from '../ResetGameProgressSectionCard';

export const SettingsRoot: FC = () => {
  const {
    props: { can },
  } = usePage<SettingsPageProps>();

  return (
    <div className="flex flex-col">
      <h1>Settings</h1>

      <div className="flex flex-col gap-4">
        <ProfileSectionCard />
        <NotificationsSectionCard />
        <PreferencesSectionCard />

        {can.manipulateApiKeys ? <KeysSectionCard /> : null}

        <ChangePasswordSectionCard />
        <ChangeEmailAddressSectionCard />
        <ResetGameProgressSectionCard />
        <DeleteAccountSectionCard />
      </div>
    </div>
  );
};
