import { Head } from '@inertiajs/react';

import { AppLayout } from '@/common/layouts/AppLayout';
import type { AppPage } from '@/common/models';
import { WantToPlayGamesRoot } from '@/features/game-list/components/WantToPlayGamesMainRoot';

const WantToPlayGames: AppPage = () => {
  return (
    <>
      <Head title="TODO">
        <meta name="description" content="TODO" />
      </Head>

      <div className="container">
        <AppLayout.Main>
          <WantToPlayGamesRoot />
        </AppLayout.Main>
      </div>
    </>
  );
};

WantToPlayGames.layout = (page) => <AppLayout withSidebar={false}>{page}</AppLayout>;

export default WantToPlayGames;
