import type { FC } from 'react';

import { AvatarSection } from '../AvatarSection/AvatarSection';
import { SiteAwardsSection } from '../SiteAwardsSection';

export const SettingsSidebar: FC = () => {
  return (
    <div className="flex flex-col gap-8">
      <SiteAwardsSection />

      <hr className="border-neutral-700 light:border-neutral-300" />

      <AvatarSection />
    </div>
  );
};
