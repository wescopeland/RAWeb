import { Head } from '@inertiajs/react';

import { AppLayout } from '@/common/layouts/AppLayout';
import type { AppPage } from '@/common/models';
import { SettingsRoot } from '@/features/user/settings/components/+root';

const Settings: AppPage = () => {
  return (
    <>
      <Head title="TODO">
        <meta name="description" content="TODO" />
      </Head>

      <div className="container">
        <AppLayout.Main>
          <SettingsRoot />
        </AppLayout.Main>
      </div>

      <AppLayout.Sidebar>
        <p>hello world</p>
      </AppLayout.Sidebar>
    </>
  );
};

Settings.layout = (page) => <AppLayout withSidebar={true}>{page}</AppLayout>;

export default Settings;
