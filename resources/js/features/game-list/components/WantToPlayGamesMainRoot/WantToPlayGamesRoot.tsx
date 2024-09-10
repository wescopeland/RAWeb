import type { FC } from 'react';

import { UserHeading } from '@/common/components/UserHeading';
import { usePageProps } from '@/common/hooks/usePageProps';

import { WantToPlayGamesDataTable } from '../WantToPlayGamesDataTable';

export const WantToPlayGamesRoot: FC = () => {
  const { auth } = usePageProps<App.Community.Data.UserGameListPageProps>();

  if (!auth?.user) {
    return null;
  }

  return (
    <div>
      <div id="pagination-scroll-target" className="scroll-mt-16">
        <UserHeading user={auth.user}>Want to Play Games</UserHeading>
      </div>

      <WantToPlayGamesDataTable />
    </div>
  );
};
