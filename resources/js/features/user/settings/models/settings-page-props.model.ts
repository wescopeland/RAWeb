import {} from 'type-fest';

import type { AppGlobalProps } from '@/common/models';

export interface SettingsPageProps extends AppGlobalProps {
  user: Required<
    Pick<App.Data.User, 'apiKey' | 'emailAddress' | 'motto' | 'userWallActive' | 'websitePrefs'>
  > &
    Pick<App.Data.User, 'deleteRequested'>;
}
