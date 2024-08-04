import {} from 'type-fest';

import type { AppGlobalProps } from '@/common/models';

type LazyLoadedUserProps = Pick<
  App.Data.User,
  'apiKey' | 'emailAddress' | 'motto' | 'userWallActive' | 'visibleRole' | 'websitePrefs'
>;

export interface SettingsPageProps extends AppGlobalProps {
  user: Required<LazyLoadedUserProps> & Pick<App.Data.User, 'deleteRequested'>;

  can: {
    manipulateApiKeys: boolean;
    updateAvatar: boolean;
    updateMotto: boolean;
  };
}
