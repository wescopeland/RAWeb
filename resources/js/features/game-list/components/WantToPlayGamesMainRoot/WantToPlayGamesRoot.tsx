import { dehydrate, HydrationBoundary, QueryClient } from '@tanstack/react-query';
import type { FC } from 'react';

import { UserHeading } from '@/common/components/UserHeading';
import { usePageProps } from '@/common/hooks/usePageProps';

import { WantToPlayGamesDataTable } from '../WantToPlayGamesDataTable';

export const WantToPlayGamesRoot: FC = () => {
  const { auth, paginatedGameListEntries } =
    usePageProps<App.Community.Data.UserGameListPageProps>();

  if (!auth?.user) {
    return null;
  }

  const queryClient = new QueryClient();
  queryClient.setQueryData(
    /**
     * TODO
     * These state values should be lifted up from the table somehow, not duplicated.
     * They really shouldn't be passed down to the table as props though. Maybe use jotai?
     */
    ['data', { pageIndex: 0, pageSize: 25 }, [{ id: 'title', desc: false }], []],
    paginatedGameListEntries,
  );

  return (
    <div>
      <div id="pagination-scroll-target" className="scroll-mt-16">
        <UserHeading user={auth.user}>Want to Play Games</UserHeading>
      </div>

      <HydrationBoundary state={dehydrate(queryClient)}>
        <WantToPlayGamesDataTable />
      </HydrationBoundary>
    </div>
  );
};
